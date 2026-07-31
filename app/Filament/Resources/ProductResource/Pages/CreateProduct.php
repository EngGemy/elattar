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

    /** لقطة كميات الفورم قبل إنشاء السجل */
    private ?array $pendingVariantStocks = null;

    protected function beforeCreate(): void
    {
        $this->pendingVariantStocks = $this->form->getRawState()['variants'] ?? [];
    }

    protected function handleRecordCreation(array $data): Model
    {
        // المتغيّرات تُحفظ بعد هذه الخطوة عبر saveRelationships
        return parent::handleRecordCreation($data);
    }

    protected function afterCreate(): void
    {
        if ($this->pendingVariantStocks === null || ! $this->record) {
            return;
        }

        $this->syncVariantStocks($this->record, $this->pendingVariantStocks);
        $this->pendingVariantStocks = null;
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

            if ($row['stock_qty'] === null || $row['stock_qty'] === '') {
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
