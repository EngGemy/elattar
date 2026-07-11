<?php

declare(strict_types=1);

namespace App\Domain\Pos\Actions;

use App\Domain\Pos\Models\RegisterSession;
use App\Domain\Sales\Actions\FulfillOrderAction;
use App\Domain\Sales\Actions\PlaceOrderAction;
use App\Domain\Sales\Actions\RecordPaymentAction;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\States\Confirmed;
use App\Domain\Sales\States\Processing;
use App\Domain\Shared\Enums\PaymentMethod;
use App\Domain\Shared\Enums\SalesChannel;
use App\Domain\Shared\ValueObjects\Money;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * بيع نقطة البيع = طلب + دفع + صرف فوري، في معاملة واحدة.
 *
 * لا مخزون منفصل للـ POS. نفس StockMovement التي يستخدمها المتجر الإلكتروني.
 * هذا يمنع أشهر خطأ في أنظمة التجزئة: كتابان للمخزون لا يتطابقان أبدًا.
 */
final class CheckoutPosAction
{
    public function __construct(
        private PlaceOrderAction $placeOrder,
        private RecordPaymentAction $recordPayment,
        private FulfillOrderAction $fulfillOrder,
    ) {}

    /**
     * @param array<int, array{variant_id:int, qty:float, discount_minor?:int}> $items
     * @param array<int, array{method:string, amount_minor:int, tendered_minor?:int, reference?:string}> $payments
     */
    public function execute(
        RegisterSession $session,
        array $items,
        array $payments,
        ?int $customerId = null,
        ?string $idempotencyKey = null,
    ): Order {
        if (! $session->isOpen()) {
            throw new RuntimeException('لا يمكن البيع — الشيفت مغلق.');
        }

        return DB::transaction(function () use ($session, $items, $payments, $customerId, $idempotencyKey) {
            $order = $this->placeOrder->execute(
                items:          $items,
                warehouseId:    (int) $session->register->warehouse_id,
                customerId:     $customerId,
                channel:        SalesChannel::Pos,
                idempotencyKey: $idempotencyKey,
            );

            // تحقق: المدفوع نقدًا يغطي الإجمالي — المبلغ المسجَّل = إجمالي الطلب من السيرفر
            foreach ($payments as $p) {
                $tendered = isset($p['tendered_minor']) ? Money::ofMinor((int) $p['tendered_minor']) : null;

                if (PaymentMethod::from($p['method']) === PaymentMethod::Cash && $tendered?->minor < $order->total_minor->minor) {
                    throw new RuntimeException(
                        "المدفوع ({$tendered?->minor}) أقل من إجمالي الفاتورة ({$order->total_minor->minor})."
                    );
                }

                $this->recordPayment->execute(
                    order:             $order,
                    method:            PaymentMethod::from($p['method']),
                    amount:            $order->total_minor,
                    tendered:          $tendered,
                    registerSessionId: (int) $session->id,
                    reference:         $p['reference'] ?? null,
                );
            }

            // البيع الحضوري: العميل يأخذ البضاعة فورًا
            $order->status->transitionTo(Confirmed::class);
            $order->refresh()->status->transitionTo(Processing::class);

            return $this->fulfillOrder->execute($order->fresh());
        }, attempts: 3);
    }
}
