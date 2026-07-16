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

        $this->syncVariantStocks($rawVariants);

        return $record;
    }

    /** @param  array<string, mixed>  $rows */
    private function syncVariantStocks(array $rows): void
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

            $variant = $this->record->variants()->where('sku', $sku)->first();
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
