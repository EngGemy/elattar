<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Domain\Inventory\Actions\SetVariantStockAction;
use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $rawVariants = $this->form->getRawState()['variants'] ?? [];

        $record = parent::handleRecordCreation($data);

        // Filament قد لا يعيّن $this->record بعد — نستخدم الـ model المُرجع مباشرة
        $this->syncVariantStocks($record, $rawVariants);

        return $record;
    }

    /**
     * @param  array<string, mixed>  $rows
     */
    private function syncVariantStocks(Model $record, array $rows): void
    {
        $setter = app(SetVariantStockAction::class);

        foreach ($rows as $row) {
            if (! is_array($row) || ! array_key_exists('stock_qty', $row)) {
                continue;
            }

            $sku = $row['sku'] ?? null;
            if (! $sku) {
                continue;
            }

            $variant = $record->variants()->where('sku', $sku)->first();
            if (! $variant) {
                continue;
            }

            $setter->execute(
                variant: $variant,
                targetQty: max(0, (float) $row['stock_qty']),
                note: 'رصيد افتتاحي من إنشاء المنتج',
            );
        }
    }
}
