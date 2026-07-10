<?php

declare(strict_types=1);

namespace App\Domain\Sales\Actions;

use App\Domain\Sales\Models\Invoice;
use App\Domain\Sales\Models\Order;
use Illuminate\Support\Facades\DB;

/** إصدار فاتورة HTML للطلب — يُنشأ عند الطلب أو عند أول عرض */
final class IssueInvoiceAction
{
    public function execute(Order $order): Invoice
    {
        $existing = $order->invoice()->latest()->first();

        if ($existing) {
            return $existing;
        }

        return DB::transaction(function () use ($order) {
            return Invoice::create([
                'number'      => Invoice::nextNumber(),
                'order_id'    => $order->id,
                'total_minor' => (int) $order->getRawOriginal('total_minor'),
                'issued_at'   => $order->placed_at ?? now(),
            ]);
        });
    }
}
