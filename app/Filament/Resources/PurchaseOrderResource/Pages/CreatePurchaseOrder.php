<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Domain\Purchasing\Models\PurchaseOrder;
use App\Filament\Resources\PurchaseOrderResource;
use Filament\Resources\Pages\CreateRecord;

class CreatePurchaseOrder extends CreateRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected static ?string $title = 'أمر شراء جديد';

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['number']     = PurchaseOrder::nextNumber();
        $data['created_by'] = auth()->id();
        $data['status']     = 'draft';

        return $data;
    }

    protected function afterCreate(): void
    {
        $this->recalculateTotals();
    }

    protected function recalculateTotals(): void
    {
        $order    = $this->record->fresh('lines');
        $subtotal = 0;

        foreach ($order->lines as $line) {
            $lineTotal = (int) round((float) $line->qty_ordered * (int) $line->getRawOriginal('unit_cost_minor'));
            $line->update(['line_total_minor' => $lineTotal]);
            $subtotal += $lineTotal;
        }

        $order->update([
            'subtotal_minor' => $subtotal,
            'total_minor'    => $subtotal,
        ]);
    }
}
