<?php

namespace App\Filament\Resources\PurchaseOrderResource\Pages;

use App\Filament\Resources\PurchaseOrderResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditPurchaseOrder extends EditRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()->label('حذف')];
    }

    protected function afterSave(): void
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
