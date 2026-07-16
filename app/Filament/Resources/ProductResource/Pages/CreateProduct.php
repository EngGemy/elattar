<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Domain\Inventory\Actions\SetVariantStockAction;
use App\Filament\Resources\ProductResource;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        $this->syncVariantStocks();
    }

    private function syncVariantStocks(): void
    {
        /** @var SetVariantStockAction $setter */
        $setter = app(SetVariantStockAction::class);
        $rows   = $this->data['variants'] ?? [];

        foreach ($rows as $row) {
            if (! array_key_exists('stock_qty', $row)) {
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

            $qty = (float) $row['stock_qty'];
            $setter->execute(
                variant: $variant,
                targetQty: max(0, $qty),
                note: 'رصيد افتتاحي من إنشاء المنتج',
            );
        }
    }
}
