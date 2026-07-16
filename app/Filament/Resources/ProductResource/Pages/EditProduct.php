<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Domain\Inventory\Actions\SetVariantStockAction;
use App\Domain\Inventory\Models\StockLevel;
use App\Domain\Inventory\Models\Warehouse;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [Actions\DeleteAction::make()->label('حذف')];
    }

    protected function afterFill(): void
    {
        $warehouseId = Warehouse::query()->where('is_default', true)->value('id');
        $variants    = $this->data['variants'] ?? [];

        if (! is_array($variants) || ! $warehouseId) {
            return;
        }

        foreach ($variants as $i => $variant) {
            $variantId = $variant['id'] ?? null;
            $this->data['variants'][$i]['stock_qty'] = $variantId
                ? (float) (StockLevel::query()
                    ->where('variant_id', $variantId)
                    ->where('warehouse_id', $warehouseId)
                    ->value('on_hand') ?? 0)
                : 0.0;
        }
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        return $data;
    }

    protected function afterSave(): void
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

            $setter->execute(
                variant: $variant,
                targetQty: (float) $row['stock_qty'],
                note: 'تعديل كمية من تعديل المنتج',
            );
        }
    }
}
