<?php

declare(strict_types=1);

namespace App\Domain\Reports\Actions;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/** تقارير تقرأ من الجداول المُجمَّعة — سريعة بغضّ النظر عن حجم البيانات */
final class SalesReportAction
{
    /** تقرير المبيعات مع مقارنة الفترة السابقة */
    public function summary(CarbonInterface $from, CarbonInterface $to): array
    {
        $current = $this->aggregate($from, $to);

        $days     = $from->diffInDays($to) + 1;
        $prevFrom = $from->copy()->subDays($days);
        $prevTo   = $from->copy()->subDay();
        $previous = $this->aggregate($prevFrom, $prevTo);

        return [
            'current'  => $current,
            'previous' => $previous,
            'growth'   => [
                'net'    => $this->pctChange($previous['net_minor'],    $current['net_minor']),
                'profit' => $this->pctChange($previous['profit_minor'], $current['profit_minor']),
                'orders' => $this->pctChange($previous['orders_count'], $current['orders_count']),
                'aov'    => $this->pctChange($previous['aov_minor'],    $current['aov_minor']),
            ],
        ];
    }

    private function aggregate(CarbonInterface $from, CarbonInterface $to): array
    {
        $row = DB::table('daily_sales_summary')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('
                COALESCE(SUM(orders_count),0)   AS orders_count,
                COALESCE(SUM(items_qty),0)      AS items_qty,
                COALESCE(SUM(gross_minor),0)    AS gross_minor,
                COALESCE(SUM(discount_minor),0) AS discount_minor,
                COALESCE(SUM(tax_minor),0)      AS tax_minor,
                COALESCE(SUM(net_minor),0)      AS net_minor,
                COALESCE(SUM(cogs_minor),0)     AS cogs_minor,
                COALESCE(SUM(profit_minor),0)   AS profit_minor
            ')->first();

        $data = array_map(fn ($value) => (float) $value, (array) $row);

        if ($data['orders_count'] == 0.0 && $this->hasLiveOrders($from, $to)) {
            return $this->aggregateLive($from, $to);
        }

        $data['aov_minor'] = $data['orders_count'] > 0
            ? (int) round($data['net_minor'] / $data['orders_count'])
            : 0;

        $data['gp_percent'] = $data['net_minor'] > 0
            ? round($data['profit_minor'] / $data['net_minor'] * 100, 2)
            : 0.0;

        return $data;
    }

    private function hasLiveOrders(CarbonInterface $from, CarbonInterface $to): bool
    {
        return DB::table('orders')
            ->whereBetween(DB::raw('DATE(placed_at)'), [$from->toDateString(), $to->toDateString()])
            ->whereNotIn('status', ['cancelled', 'returned'])
            ->whereNull('deleted_at')
            ->exists();
    }

    /** قراءة مباشرة من الطلبات عند غياب بيانات الـ rollup */
    private function aggregateLive(CarbonInterface $from, CarbonInterface $to): array
    {
        $row = DB::table('orders AS o')
            ->leftJoin('order_lines AS ol', 'ol.order_id', '=', 'o.id')
            ->whereBetween(DB::raw('DATE(o.placed_at)'), [$from->toDateString(), $to->toDateString()])
            ->whereNotIn('o.status', ['cancelled', 'returned'])
            ->whereNull('o.deleted_at')
            ->selectRaw('
                COUNT(DISTINCT o.id)                     AS orders_count,
                COALESCE(SUM(ol.qty), 0)                 AS items_qty,
                COALESCE(SUM(o.subtotal_minor), 0)       AS gross_minor,
                COALESCE(SUM(o.discount_minor), 0)       AS discount_minor,
                COALESCE(SUM(o.tax_minor), 0)            AS tax_minor,
                COALESCE(SUM(o.subtotal_minor - o.discount_minor), 0) AS net_minor,
                COALESCE(SUM(o.cogs_minor), 0)           AS cogs_minor,
                COALESCE(SUM(o.subtotal_minor - o.discount_minor - o.cogs_minor), 0) AS profit_minor
            ')
            ->first();

        $data = array_map(fn ($value) => (float) $value, (array) $row);
        $data['aov_minor'] = $data['orders_count'] > 0
            ? (int) round((float) (DB::table('orders')
                ->whereBetween(DB::raw('DATE(placed_at)'), [$from->toDateString(), $to->toDateString()])
                ->whereNotIn('status', ['cancelled', 'returned'])
                ->whereNull('deleted_at')
                ->avg('total_minor') ?? 0))
            : 0;
        $data['gp_percent'] = $data['net_minor'] > 0
            ? round($data['profit_minor'] / $data['net_minor'] * 100, 2)
            : 0.0;

        return $data;
    }

    private function pctChange(float $old, float $new): float
    {
        return $old == 0.0 ? ($new > 0 ? 100.0 : 0.0) : round(($new - $old) / abs($old) * 100, 2);
    }

    /** أفضل المنتجات مبيعًا — بالإيراد لا بالكمية */
    public function bestSellers(CarbonInterface $from, CarbonInterface $to, int $limit = 20): array
    {
        $rows = DB::table('daily_product_summary AS dps')
            ->join('product_variants AS pv', 'pv.id', '=', 'dps.variant_id')
            ->join('products AS p', 'p.id', '=', 'pv.product_id')
            ->join('categories AS c', 'c.id', '=', 'p.category_id')
            ->whereBetween('dps.date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('dps.variant_id', 'p.name', 'pv.sku', 'c.name')
            ->selectRaw('
                p.name AS product_name,
                pv.sku,
                c.name AS category_name,
                SUM(dps.qty_sold)      AS qty_sold,
                SUM(dps.revenue_minor) AS revenue_minor,
                SUM(dps.profit_minor)  AS profit_minor,
                CASE WHEN SUM(dps.revenue_minor) > 0
                     THEN ROUND(SUM(dps.profit_minor) * 100.0 / SUM(dps.revenue_minor), 2)
                     ELSE 0 END AS gp_percent
            ')
            ->orderByDesc('revenue_minor')
            ->limit($limit)
            ->get();

        if ($rows->isEmpty() && $this->hasLiveOrders($from, $to)) {
            return $this->bestSellersLive($from, $to, $limit);
        }

        return $rows->toArray();
    }

    /** @return array<int, object> */
    private function bestSellersLive(CarbonInterface $from, CarbonInterface $to, int $limit): array
    {
        return DB::table('order_lines AS ol')
            ->join('orders AS o', 'o.id', '=', 'ol.order_id')
            ->join('product_variants AS pv', 'pv.id', '=', 'ol.variant_id')
            ->join('products AS p', 'p.id', '=', 'pv.product_id')
            ->join('categories AS c', 'c.id', '=', 'p.category_id')
            ->whereBetween(DB::raw('DATE(o.placed_at)'), [$from->toDateString(), $to->toDateString()])
            ->whereNotIn('o.status', ['cancelled', 'returned'])
            ->whereNull('o.deleted_at')
            ->groupBy('ol.variant_id', 'p.name', 'pv.sku', 'c.name')
            ->selectRaw('
                p.name AS product_name,
                pv.sku,
                c.name AS category_name,
                SUM(ol.qty) AS qty_sold,
                SUM(ol.line_total_minor - ol.tax_minor) AS revenue_minor,
                SUM(ol.line_total_minor - ol.tax_minor) -
                SUM(CASE WHEN ol.unit IN (\'gram\',\'ml\')
                     THEN ol.cost_minor * ol.qty / 1000
                     ELSE ol.cost_minor * ol.qty END) AS profit_minor,
                CASE WHEN SUM(ol.line_total_minor - ol.tax_minor) > 0
                     THEN ROUND((SUM(ol.line_total_minor - ol.tax_minor) -
                     SUM(CASE WHEN ol.unit IN (\'gram\',\'ml\')
                          THEN ol.cost_minor * ol.qty / 1000
                          ELSE ol.cost_minor * ol.qty END)) * 100.0 /
                     SUM(ol.line_total_minor - ol.tax_minor), 2)
                     ELSE 0 END AS gp_percent
            ')
            ->orderByDesc('revenue_minor')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /** هامش الربح لكل تصنيف */
    public function marginByCategory(CarbonInterface $from, CarbonInterface $to): array
    {
        $rows = DB::table('daily_product_summary AS dps')
            ->join('product_variants AS pv', 'pv.id', '=', 'dps.variant_id')
            ->join('products AS p', 'p.id', '=', 'pv.product_id')
            ->join('categories AS c', 'c.id', '=', 'p.category_id')
            ->whereBetween('dps.date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('c.id', 'c.name')
            ->selectRaw('
                c.name AS category_name,
                SUM(dps.revenue_minor) AS revenue_minor,
                SUM(dps.cogs_minor)    AS cogs_minor,
                SUM(dps.profit_minor)  AS profit_minor,
                CASE WHEN SUM(dps.revenue_minor) > 0
                     THEN ROUND(SUM(dps.profit_minor) * 100.0 / SUM(dps.revenue_minor), 2)
                     ELSE 0 END AS gp_percent
            ')
            ->orderByDesc('profit_minor')
            ->get();

        if ($rows->isEmpty() && $this->hasLiveOrders($from, $to)) {
            return $this->marginByCategoryLive($from, $to);
        }

        return $rows->toArray();
    }

    /** @return array<int, object> */
    private function marginByCategoryLive(CarbonInterface $from, CarbonInterface $to): array
    {
        return DB::table('order_lines AS ol')
            ->join('orders AS o', 'o.id', '=', 'ol.order_id')
            ->join('product_variants AS pv', 'pv.id', '=', 'ol.variant_id')
            ->join('products AS p', 'p.id', '=', 'pv.product_id')
            ->join('categories AS c', 'c.id', '=', 'p.category_id')
            ->whereBetween(DB::raw('DATE(o.placed_at)'), [$from->toDateString(), $to->toDateString()])
            ->whereNotIn('o.status', ['cancelled', 'returned'])
            ->whereNull('o.deleted_at')
            ->groupBy('c.id', 'c.name')
            ->selectRaw('
                c.name AS category_name,
                SUM(ol.line_total_minor - ol.tax_minor) AS revenue_minor,
                SUM(CASE WHEN ol.unit IN (\'gram\',\'ml\')
                     THEN ol.cost_minor * ol.qty / 1000
                     ELSE ol.cost_minor * ol.qty END) AS cogs_minor,
                SUM(ol.line_total_minor - ol.tax_minor) -
                SUM(CASE WHEN ol.unit IN (\'gram\',\'ml\')
                     THEN ol.cost_minor * ol.qty / 1000
                     ELSE ol.cost_minor * ol.qty END) AS profit_minor,
                CASE WHEN SUM(ol.line_total_minor - ol.tax_minor) > 0
                     THEN ROUND((SUM(ol.line_total_minor - ol.tax_minor) -
                     SUM(CASE WHEN ol.unit IN (\'gram\',\'ml\')
                          THEN ol.cost_minor * ol.qty / 1000
                          ELSE ol.cost_minor * ol.qty END)) * 100.0 /
                     SUM(ol.line_total_minor - ol.tax_minor), 2)
                     ELSE 0 END AS gp_percent
            ')
            ->orderByDesc('profit_minor')
            ->get()
            ->toArray();
    }

    /** تقييم المخزون + المخزون الراكد */
    public function inventoryValuation(int $deadStockDays = 90): array
    {
        return DB::table('stock_levels AS sl')
            ->join('product_variants AS pv', 'pv.id', '=', 'sl.variant_id')
            ->join('products AS p', 'p.id', '=', 'pv.product_id')
            ->join('warehouses AS w', 'w.id', '=', 'sl.warehouse_id')
            ->where('sl.on_hand', '>', 0)
            ->selectRaw('
                w.name  AS warehouse_name,
                p.name  AS product_name,
                pv.sku,
                sl.on_hand,
                sl.reserved,
                sl.on_hand - sl.reserved AS available,
                pv.cost_minor,
                ROUND(sl.on_hand * pv.cost_minor) AS value_minor,
                sl.last_movement_at,
                CASE WHEN sl.last_movement_at < ? THEN 1 ELSE 0 END AS is_dead_stock
            ', [now()->subDays($deadStockDays)])
            ->orderByDesc('value_minor')
            ->get()->toArray();
    }

    /** أداء الكاشيرين — العجز والزيادة */
    public function cashierPerformance(CarbonInterface $from, CarbonInterface $to): array
    {
        return DB::table('register_sessions AS rs')
            ->join('users AS u', 'u.id', '=', 'rs.user_id')
            ->whereBetween('rs.opened_at', [$from, $to])
            ->where('rs.status', 'closed')
            ->groupBy('u.id', 'u.name')
            ->selectRaw('
                u.name AS cashier_name,
                COUNT(rs.id)                  AS sessions_count,
                SUM(rs.orders_count)          AS orders_count,
                SUM(rs.cash_sales_minor)      AS cash_sales_minor,
                SUM(rs.card_sales_minor)      AS card_sales_minor,
                SUM(rs.variance_minor)        AS total_variance_minor,
                SUM(CASE WHEN rs.variance_minor < 0 THEN 1 ELSE 0 END) AS shortage_count
            ')
            ->orderBy('total_variance_minor')
            ->get()->toArray();
    }

    /** اتجاه المبيعات اليومي */
    public function dailyTrend(CarbonInterface $from, CarbonInterface $to): array
    {
        $rows = DB::table('daily_sales_summary')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('date')
            ->selectRaw('
                date,
                SUM(orders_count)   AS orders_count,
                SUM(net_minor)      AS net_minor,
                SUM(profit_minor)   AS profit_minor,
                SUM(cogs_minor)     AS cogs_minor,
                SUM(discount_minor) AS discount_minor
            ')
            ->orderBy('date')
            ->get();

        if ($rows->isEmpty() && $this->hasLiveOrders($from, $to)) {
            return $this->dailyTrendLive($from, $to);
        }

        return $rows->toArray();
    }

    /** @return array<int, object> */
    private function dailyTrendLive(CarbonInterface $from, CarbonInterface $to): array
    {
        return DB::table('orders AS o')
            ->whereBetween(DB::raw('DATE(o.placed_at)'), [$from->toDateString(), $to->toDateString()])
            ->whereNotIn('o.status', ['cancelled', 'returned'])
            ->whereNull('o.deleted_at')
            ->groupBy(DB::raw('DATE(o.placed_at)'))
            ->selectRaw('
                DATE(o.placed_at) AS date,
                COUNT(*) AS orders_count,
                COALESCE(SUM(o.subtotal_minor - o.discount_minor), 0) AS net_minor,
                COALESCE(SUM(o.subtotal_minor - o.discount_minor - o.cogs_minor), 0) AS profit_minor,
                COALESCE(SUM(o.cogs_minor), 0) AS cogs_minor,
                COALESCE(SUM(o.discount_minor), 0) AS discount_minor
            ')
            ->orderBy('date')
            ->get()
            ->toArray();
    }

    /** توزيع المبيعات حسب القناة (POS / أونلاين) */
    public function channelBreakdown(CarbonInterface $from, CarbonInterface $to): array
    {
        $rows = DB::table('daily_sales_summary')
            ->whereBetween('date', [$from->toDateString(), $to->toDateString()])
            ->groupBy('channel')
            ->selectRaw('
                channel,
                SUM(orders_count) AS orders_count,
                SUM(net_minor)    AS net_minor,
                SUM(profit_minor) AS profit_minor,
                CASE WHEN SUM(net_minor) > 0
                     THEN ROUND(SUM(profit_minor) * 100.0 / SUM(net_minor), 2)
                     ELSE 0 END AS gp_percent
            ')
            ->orderByDesc('net_minor')
            ->get();

        if ($rows->isEmpty() && $this->hasLiveOrders($from, $to)) {
            return $this->channelBreakdownLive($from, $to);
        }

        return $rows->toArray();
    }

    /** @return array<int, object> */
    private function channelBreakdownLive(CarbonInterface $from, CarbonInterface $to): array
    {
        return DB::table('orders AS o')
            ->whereBetween(DB::raw('DATE(o.placed_at)'), [$from->toDateString(), $to->toDateString()])
            ->whereNotIn('o.status', ['cancelled', 'returned'])
            ->whereNull('o.deleted_at')
            ->groupBy('o.channel')
            ->selectRaw('
                o.channel AS channel,
                COUNT(*) AS orders_count,
                COALESCE(SUM(o.subtotal_minor - o.discount_minor), 0) AS net_minor,
                COALESCE(SUM(o.subtotal_minor - o.discount_minor - o.cogs_minor), 0) AS profit_minor,
                CASE WHEN SUM(o.subtotal_minor - o.discount_minor) > 0
                     THEN ROUND(SUM(o.subtotal_minor - o.discount_minor - o.cogs_minor) * 100.0 /
                          SUM(o.subtotal_minor - o.discount_minor), 2)
                     ELSE 0 END AS gp_percent
            ')
            ->orderByDesc('net_minor')
            ->get()
            ->toArray();
    }
}
