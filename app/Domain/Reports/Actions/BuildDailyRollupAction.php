<?php

declare(strict_types=1);

namespace App\Domain\Reports\Actions;

use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/**
 * تجميع يومي (Rollup) يعمل الساعة 00:05.
 *
 * التقارير تقرأ من هذه الجداول لا من orders/order_lines مباشرة.
 * الفرق: تقرير "أفضل المنتجات لآخر سنة" يمسح 365 صفًا بدل مليون سطر فاتورة.
 */
final class BuildDailyRollupAction
{
    public function execute(CarbonInterface $date): void
    {
        $day = $date->toDateString();

        DB::transaction(function () use ($day) {
            // ── ملخص المبيعات: لكل (يوم × مخزن × قناة)
            DB::table('daily_sales_summary')->where('date', $day)->delete();

            DB::statement("
                INSERT INTO daily_sales_summary
                    (date, warehouse_id, channel, orders_count, items_qty,
                     gross_minor, discount_minor, tax_minor, net_minor,
                     cogs_minor, profit_minor, aov_minor, created_at, updated_at)
                SELECT
                    ?                                        AS date,
                    o.warehouse_id,
                    o.channel,
                    COUNT(DISTINCT o.id)                     AS orders_count,
                    COALESCE(SUM(ol.qty), 0)                 AS items_qty,
                    COALESCE(SUM(o.subtotal_minor), 0)       AS gross_minor,
                    COALESCE(SUM(o.discount_minor), 0)       AS discount_minor,
                    COALESCE(SUM(o.tax_minor), 0)            AS tax_minor,
                    COALESCE(SUM(o.subtotal_minor - o.discount_minor), 0) AS net_minor,
                    COALESCE(SUM(o.cogs_minor), 0)           AS cogs_minor,
                    COALESCE(SUM(o.subtotal_minor - o.discount_minor - o.cogs_minor), 0) AS profit_minor,
                    CASE WHEN COUNT(DISTINCT o.id) = 0 THEN 0
                         ELSE COALESCE(SUM(o.total_minor), 0) / COUNT(DISTINCT o.id) END AS aov_minor,
                    NOW(), NOW()
                FROM orders o
                LEFT JOIN order_lines ol ON ol.order_id = o.id
                WHERE DATE(o.placed_at) = ?
                  AND o.status NOT IN ('cancelled', 'returned')
                  AND o.deleted_at IS NULL
                GROUP BY o.warehouse_id, o.channel
            ", [$day, $day]);

            // ── ملخص المنتجات: لكل (يوم × متغيّر)
            DB::table('daily_product_summary')->where('date', $day)->delete();

            DB::statement("
                INSERT INTO daily_product_summary
                    (date, variant_id, qty_sold, revenue_minor, cogs_minor, profit_minor)
                SELECT
                    ?                                   AS date,
                    ol.variant_id,
                    SUM(ol.qty)                         AS qty_sold,
                    SUM(ol.line_total_minor - ol.tax_minor) AS revenue_minor,
                    SUM(
                        CASE WHEN ol.unit IN ('gram','ml')
                             THEN ol.cost_minor * ol.qty / 1000
                             ELSE ol.cost_minor * ol.qty END
                    )                                   AS cogs_minor,
                    SUM(ol.line_total_minor - ol.tax_minor) -
                    SUM(
                        CASE WHEN ol.unit IN ('gram','ml')
                             THEN ol.cost_minor * ol.qty / 1000
                             ELSE ol.cost_minor * ol.qty END
                    )                                   AS profit_minor
                FROM order_lines ol
                JOIN orders o ON o.id = ol.order_id
                WHERE DATE(o.placed_at) = ?
                  AND o.status NOT IN ('cancelled', 'returned')
                  AND o.deleted_at IS NULL
                GROUP BY ol.variant_id
            ", [$day, $day]);
        });
    }
}
