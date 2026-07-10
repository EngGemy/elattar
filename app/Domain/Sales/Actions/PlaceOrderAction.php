<?php

declare(strict_types=1);

namespace App\Domain\Sales\Actions;

use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Inventory\Actions\ReserveStockAction;
use App\Domain\Pricing\Models\Coupon;
use App\Domain\Pricing\Models\CouponUsage;
use App\Domain\Sales\Actions\IssueInvoiceAction;
use App\Domain\Sales\Events\OrderPlaced;
use App\Domain\Sales\Exceptions\InvalidOrderException;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\OrderLine;
use App\Domain\Sales\Models\OrderStatusHistory;
use App\Domain\Shared\Enums\SalesChannel;
use App\Domain\Shared\Enums\UnitOfMeasure;
use App\Domain\Shared\ValueObjects\Money;
use App\Domain\Shared\ValueObjects\Quantity;
use Illuminate\Support\Facades\DB;

/**
 * إنشاء طلب.
 *
 * ترتيب العمليات مقصود:
 *  1) idempotency  — منع الطلب المزدوج
 *  2) حجز المخزون  — يفشل مبكرًا لو الكمية غير كافية
 *  3) تجميد الأسعار والتكاليف في الأسطر
 *  4) الاحتساب (subtotal → discount → tax → shipping)
 */
final class PlaceOrderAction
{
    public function __construct(
        private ReserveStockAction $reserveStock,
        private IssueInvoiceAction $issueInvoice,
    ) {}

    /**
     * @param array<int, array{variant_id:int, qty:float, discount_minor?:int}> $items
     */
    public function execute(
        array $items,
        int $warehouseId,
        ?int $customerId = null,
        SalesChannel $channel = SalesChannel::Online,
        ?array $shippingAddress = null,
        ?int $shippingMethodId = null,
        ?string $couponCode = null,
        ?string $idempotencyKey = null,
        ?string $notes = null,
    ): Order {
        if (empty($items)) {
            throw new InvalidOrderException('لا يمكن إنشاء طلب بدون أصناف.');
        }

        // ── ١) الحماية من التكرار: نفس المفتاح ⟵ نفس الطلب
        if ($idempotencyKey) {
            $existing = Order::where('idempotency_key', $idempotencyKey)->first();
            if ($existing) {
                return $existing;
            }
        }

        return DB::transaction(function () use (
            $items, $warehouseId, $customerId, $channel,
            $shippingAddress, $shippingMethodId, $couponCode, $idempotencyKey, $notes
        ) {
            $order = Order::create([
                'number'           => Order::nextNumber(),
                'customer_id'      => $customerId,
                'warehouse_id'     => $warehouseId,
                'channel'          => $channel,
                'shipping_address' => $shippingAddress,
                'billing_address'  => $shippingAddress,
                'shipping_method_id' => $shippingMethodId,
                'idempotency_key'  => $idempotencyKey,
                'notes'            => $notes,
                'created_by'       => auth()->id(),
                'placed_at'        => now(),
            ]);

            // ── ٢) حجز المخزون قبل أي حساب — الفشل السريع
            $this->reserveStock->execute(
                lines:       array_map(fn ($i) => ['variant_id' => $i['variant_id'], 'qty' => $i['qty']], $items),
                warehouseId: $warehouseId,
                reference:   $order,
                ttlMinutes:  $channel === SalesChannel::Pos ? null : 30,
            );

            $subtotal     = Money::zero();
            $lineDiscount = Money::zero();
            $totalTax     = Money::zero();
            $totalCogs    = Money::zero();
            $totalWeight  = 0.0;

            // ── ٣) بناء الأسطر بلقطات مجمّدة
            foreach ($items as $item) {
                $variant = ProductVariant::with('product.taxClass')->findOrFail($item['variant_id']);
                $qty     = (float) $item['qty'];
                $qtyVo   = Quantity::of($qty, $variant->unit);

                $unitPrice = $variant->effectivePrice();
                $lineGross = $variant->priceFor($qtyVo);
                $lineDisc  = Money::ofMinor((int) ($item['discount_minor'] ?? 0));
                $lineNet   = $lineGross->minus($lineDisc)->clampToZero();

                // التكلفة — بنفس منطق التسعير بالوزن (MySQL يُرجع cost_minor كسلسلة عبر getRawOriginal)
                $costFactor = in_array($variant->unit, [UnitOfMeasure::Gram, UnitOfMeasure::Ml], true)
                    ? $qty / 1000
                    : $qty;
                $unitCostMinor = (int) ($variant->getRawOriginal('cost_minor') ?? $variant->cost()->minor);
                $lineCost = Money::ofMinor($unitCostMinor)->multipliedBy($costFactor);

                // الضريبة
                $taxRate  = $variant->product->taxRate();
                $taxClass = $variant->product->taxClass;

                if ($taxClass?->is_inclusive) {
                    // السعر شامل الضريبة: نستخرجها بالقسمة
                    $taxAmount = Money::ofMinor(
                        (int) round($lineNet->minor - ($lineNet->minor / (1 + $taxRate / 100)))
                    );
                } else {
                    $taxAmount = $lineNet->percentage($taxRate);
                }

                $lineTotal = $taxClass?->is_inclusive ? $lineNet : $lineNet->plus($taxAmount);

                OrderLine::create([
                    'order_id'            => $order->id,
                    'variant_id'          => $variant->id,
                    'sku_snapshot'        => $variant->sku,
                    'name_snapshot'       => $variant->full_name,
                    'attributes_snapshot' => $variant->attributesSnapshot(),
                    'qty'                 => $qty,
                    'unit'                => $variant->unit,
                    'unit_price_minor'    => (int) $unitPrice->minor,
                    'cost_minor'          => $unitCostMinor,
                    'line_discount_minor' => (int) $lineDisc->minor,
                    'tax_rate'            => $taxRate / 100,
                    'tax_minor'           => (int) $taxAmount->minor,
                    'line_total_minor'    => (int) $lineTotal->minor,
                ]);

                $subtotal     = $subtotal->plus($lineGross);
                $lineDiscount = $lineDiscount->plus($lineDisc);
                $totalTax     = $totalTax->plus($taxAmount);
                $totalCogs    = $totalCogs->plus($lineCost);
                $totalWeight += (int) ($variant->weight_grams ?? 0) * $qty / 1000;
            }

            // ── ٤) الكوبون على مستوى الطلب
            $orderDiscount = Money::zero();
            if ($couponCode) {
                $coupon = Coupon::where('code', $couponCode)->first();

                if (! $coupon || ! $coupon->isValidFor($subtotal, $customerId)) {
                    throw new InvalidOrderException("الكوبون «{$couponCode}» غير صالح أو منتهي الصلاحية.");
                }

                $orderDiscount = $coupon->discountFor($subtotal->minus($lineDiscount));

                CouponUsage::create([
                    'coupon_id'      => $coupon->id,
                    'customer_id'    => $customerId,
                    'order_id'       => $order->id,
                    'discount_minor' => (int) $orderDiscount->minor,
                ]);

                $coupon->increment('used_count');
                $order->coupon_code = $couponCode;
            }

            // ── ٥) الشحن
            $shipping = Money::zero();
            if ($shippingMethodId && $method = \App\Domain\Pricing\Models\ShippingMethod::find($shippingMethodId)) {
                $shipping = $method->calculate($subtotal, $totalWeight);
            }

            $totalDiscount = $lineDiscount->plus($orderDiscount);
            $total = $subtotal->minus($totalDiscount)->plus($shipping)->clampToZero();

            $order->update([
                'subtotal_minor' => (int) $subtotal->minor,
                'discount_minor' => (int) $totalDiscount->minor,
                'tax_minor'      => (int) $totalTax->minor,
                'shipping_minor' => (int) $shipping->minor,
                'total_minor'    => (int) $total->minor,
                'cogs_minor'     => (int) $totalCogs->minor,
            ]);

            OrderStatusHistory::create([
                'order_id'    => $order->id,
                'from_status' => null,
                'to_status'   => 'pending',
                'note'        => 'تم إنشاء الطلب',
                'user_id'     => auth()->id(),
            ]);

            OrderPlaced::dispatch($order->fresh('lines'));

            $this->issueInvoice->execute($order->fresh());

            return $order->fresh('lines');
        }, attempts: 3);
    }
}
