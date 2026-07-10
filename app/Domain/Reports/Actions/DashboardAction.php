<?php

declare(strict_types=1);

namespace App\Domain\Reports\Actions;

use App\Domain\Inventory\Models\StockLevel;
use App\Domain\Pos\Models\RegisterSession;
use App\Domain\Purchasing\Models\PurchaseOrder;
use App\Domain\Sales\Models\Order;
use App\Domain\Shared\Enums\SalesChannel;
use App\Filament\Resources\OrderResource;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

/** بيانات لوحة التحكم الشاملة — Odoo/SAP style */
final class DashboardAction
{
    public function __construct(private SalesReportAction $reports) {}

    /** @return array<string, mixed> */
    public function build(CarbonInterface $from, CarbonInterface $to): array
    {
        $valuation = $this->reports->inventoryValuation();
        $deadStock = array_values(array_filter($valuation, fn ($r) => $r->is_dead_stock));

        return [
            'summary'          => $this->reports->summary($from, $to),
            'dailyTrend'       => $this->reports->dailyTrend($from, $to),
            'channels'         => $this->reports->channelBreakdown($from, $to),
            'bestSellers'      => $this->reports->bestSellers($from, $to, 10),
            'byCategory'       => $this->reports->marginByCategory($from, $to),
            'cashiers'         => $this->reports->cashierPerformance($from, $to),
            'ordersPipeline'   => $this->ordersPipeline($from, $to),
            'recentOrders'     => $this->recentOrders(12),
            'pendingOnline'    => $this->pendingOnlineOrders(8),
            'lowStock'         => $this->lowStockItems(10),
            'deadStock'        => array_slice($deadStock, 0, 8),
            'deadStockCount'   => count($deadStock),
            'deadStockValue'   => array_sum(array_column($deadStock, 'value_minor')),
            'totalInventory'   => array_sum(array_column($valuation, 'value_minor')),
            'openSessions'     => $this->openRegisterSessions(),
            'purchasing'       => $this->purchasingStats(),
            'customers'        => $this->customerStats($from, $to),
            'today'            => $this->reports->summary(now()->startOfDay(), now()->endOfDay()),
            'yesterday'        => $this->reports->summary(now()->subDay()->startOfDay(), now()->subDay()->endOfDay()),
            'month'            => $this->reports->summary(now()->startOfMonth(), now()->endOfDay()),
        ];
    }

    /** @return array<int, object> */
    private function ordersPipeline(CarbonInterface $from, CarbonInterface $to): array
    {
        return DB::table('orders')
            ->whereBetween(DB::raw('DATE(placed_at)'), [$from->toDateString(), $to->toDateString()])
            ->whereNotIn('status', ['cancelled', 'returned'])
            ->whereNull('deleted_at')
            ->groupBy('status')
            ->selectRaw('status, COUNT(*) AS cnt, COALESCE(SUM(total_minor),0) AS total_minor')
            ->orderByDesc('cnt')
            ->get()
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function recentOrders(int $limit): array
    {
        return Order::query()
            ->with('customer')
            ->latest('placed_at')
            ->limit($limit)
            ->get()
            ->map(fn (Order $o) => [
                'id'       => $o->id,
                'number'   => $o->number,
                'customer' => $o->customer?->name ?? '—',
                'channel'  => $o->channel->value,
                'status'   => $o->status->label(),
                'total'    => (int) $o->getRawOriginal('total_minor'),
                'placed'   => $o->placed_at?->format('d/m H:i') ?? '—',
                'url'      => OrderResource::getUrl('view', ['record' => $o], isAbsolute: false),
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function pendingOnlineOrders(int $limit): array
    {
        return Order::query()
            ->where('status', 'pending')
            ->where('channel', SalesChannel::Online)
            ->with('customer')
            ->latest('placed_at')
            ->limit($limit)
            ->get()
            ->map(fn (Order $o) => [
                'id'       => $o->id,
                'number'   => $o->number,
                'customer' => $o->customer?->name ?? ($o->shipping_address['recipient_name'] ?? 'عميل'),
                'total'    => (int) $o->getRawOriginal('total_minor'),
                'placed'   => $o->placed_at?->diffForHumans() ?? '—',
                'url'      => OrderResource::getUrl('view', ['record' => $o], isAbsolute: false),
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function lowStockItems(int $limit): array
    {
        return StockLevel::query()
            ->lowStock()
            ->with(['variant.product', 'warehouse'])
            ->limit($limit)
            ->get()
            ->map(fn (StockLevel $s) => [
                'product'   => $s->variant->product->name ?? '—',
                'warehouse' => $s->warehouse->name ?? '—',
                'available' => $s->availableQty(),
                'reorder'   => (float) $s->reorder_point,
            ])
            ->all();
    }

    /** @return array<int, array<string, mixed>> */
    private function openRegisterSessions(): array
    {
        return RegisterSession::query()
            ->where('status', 'open')
            ->with(['user', 'register'])
            ->get()
            ->map(fn (RegisterSession $s) => [
                'cashier'  => $s->user->name ?? '—',
                'register' => $s->register->name ?? '—',
                'opened'   => $s->opened_at?->format('H:i') ?? '—',
                'orders'   => (int) $s->orders_count,
                'cash'     => (int) $s->getRawOriginal('cash_sales_minor'),
                'card'     => (int) $s->getRawOriginal('card_sales_minor'),
            ])
            ->all();
    }

    /** @return array<string, int|float> */
    private function purchasingStats(): array
    {
        $pending = PurchaseOrder::query()->whereIn('status', ['draft', 'sent', 'partial'])->count();
        $received = PurchaseOrder::query()->where('status', 'received')->where('updated_at', '>=', now()->startOfMonth())->count();

        return ['pending' => $pending, 'received_mtd' => $received];
    }

    /** @return array<string, int|float> */
    private function customerStats(CarbonInterface $from, CarbonInterface $to): array
    {
        $newCustomers = DB::table('customers')
            ->whereBetween('created_at', [$from, $to->endOfDay()])
            ->count();

        $activeBuyers = DB::table('orders')
            ->whereBetween(DB::raw('DATE(placed_at)'), [$from->toDateString(), $to->toDateString()])
            ->whereNotNull('customer_id')
            ->whereNotIn('status', ['cancelled', 'returned'])
            ->distinct('customer_id')
            ->count('customer_id');

        return [
            'total'   => (int) DB::table('customers')->where('is_active', true)->count(),
            'new'     => (int) $newCustomers,
            'buyers'  => (int) $activeBuyers,
        ];
    }

    public static function statusLabel(string $status): string
    {
        $key = str_contains($status, '\\')
            ? strtolower(class_basename($status))
            : strtolower($status);

        return match ($key) {
            'pending'    => 'معلّق',
            'confirmed'  => 'مؤكّد',
            'processing' => 'قيد التجهيز',
            'shipped'    => 'تم الشحن',
            'delivered'  => 'تم التسليم',
            'fulfilled'  => 'مكتمل',
            'cancelled'  => 'ملغي',
            'returned'   => 'مرتجع',
            default      => $status,
        };
    }
}
