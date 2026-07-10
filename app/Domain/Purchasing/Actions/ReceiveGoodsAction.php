<?php

declare(strict_types=1);

namespace App\Domain\Purchasing\Actions;

use App\Domain\Inventory\Actions\RecalculateAverageCostAction;
use App\Domain\Inventory\Actions\RecordStockMovementAction;
use App\Domain\Purchasing\Models\GoodsReceipt;
use App\Domain\Purchasing\Models\GoodsReceiptLine;
use App\Domain\Purchasing\Models\PurchaseOrder;
use App\Domain\Purchasing\Models\PurchaseOrderLine;
use App\Domain\Shared\Enums\MovementType;
use Illuminate\Support\Facades\DB;
use RuntimeException;

/**
 * استلام بضاعة من مورد.
 *
 * ⚠ ترتيب حرج: يُعاد حساب متوسط التكلفة *قبل* إدخال الكمية،
 * لأن المعادلة تحتاج الرصيد القديم. عكس الترتيب = تكلفة خاطئة.
 */
final class ReceiveGoodsAction
{
    public function __construct(
        private RecordStockMovementAction $recordMovement,
        private RecalculateAverageCostAction $recalcCost,
    ) {}

    /** @param array<int, array{po_line_id:int, qty:float, unit_cost_minor?:int}> $lines */
    public function execute(
        PurchaseOrder $po,
        array $lines,
        ?string $supplierInvoiceNo = null,
        ?string $note = null,
    ): GoodsReceipt {
        if ($po->status === 'cancelled') {
            throw new RuntimeException('لا يمكن الاستلام على أمر شراء ملغى.');
        }

        return DB::transaction(function () use ($po, $lines, $supplierInvoiceNo, $note) {
            $receipt = GoodsReceipt::create([
                'number'              => GoodsReceipt::nextNumber(),
                'purchase_order_id'   => $po->id,
                'warehouse_id'        => $po->warehouse_id,
                'supplier_invoice_no' => $supplierInvoiceNo,
                'note'                => $note,
                'received_by'         => auth()->id(),
                'received_at'         => now(),
            ]);

            foreach ($lines as $l) {
                $poLine = PurchaseOrderLine::whereKey($l['po_line_id'])
                    ->where('purchase_order_id', $po->id)
                    ->lockForUpdate()
                    ->firstOrFail();

                $qty = (float) $l['qty'];

                if ($qty <= 0) {
                    continue;
                }

                if ($qty > $poLine->qtyPending()) {
                    throw new RuntimeException(
                        "الكمية المستلمة ({$qty}) تتجاوز المتبقي ({$poLine->qtyPending()}) على أمر الشراء."
                    );
                }

                // تكلفة الاستلام قد تختلف عن تكلفة الأمر (تغيّر سعر المورد)
                $unitCost = (int) ($l['unit_cost_minor'] ?? $poLine->getRawOriginal('unit_cost_minor'));

                GoodsReceiptLine::create([
                    'goods_receipt_id'      => $receipt->id,
                    'purchase_order_line_id' => $poLine->id,
                    'variant_id'            => $poLine->variant_id,
                    'qty'                   => $qty,
                    'unit_cost_minor'       => $unitCost,
                ]);

                // ① أولًا: متوسط التكلفة (يحتاج الرصيد القديم)
                $this->recalcCost->execute($poLine->variant_id, $qty, $unitCost);

                // ② ثم: إدخال الكمية للمخزون
                $this->recordMovement->execute(
                    variantId:     $poLine->variant_id,
                    warehouseId:   $po->warehouse_id,
                    type:          MovementType::Purchase,
                    qtyDelta:      $qty,
                    unitCostMinor: $unitCost,
                    reference:     $receipt,
                );

                $poLine->increment('qty_received', $qty);
            }

            $po->load('lines')->refreshStatus();

            return $receipt->fresh('lines');
        }, attempts: 3);
    }
}
