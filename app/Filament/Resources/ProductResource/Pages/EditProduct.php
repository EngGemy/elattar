<?php

declare(strict_types=1);

namespace App\Filament\Resources\ProductResource\Pages;

use App\Domain\Inventory\Actions\SetVariantStockAction;
use App\Domain\Inventory\Models\StockLevel;
use App\Domain\Inventory\Models\Warehouse;
use App\Filament\Resources\ProductResource;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('set_stock')
                ->label('تعديل الكمية')
                ->icon('heroicon-o-archive-box')
                ->color('primary')
                ->visible(fn () => ! auth()->user()?->isCashier())
                ->form(function (): array {
                    $warehouseId = Warehouse::query()->where('is_default', true)->value('id');
                    $variants    = $this->record->variants()->with(['stockLevels' => fn ($q) => $q->where('warehouse_id', $warehouseId)])->get();

                    $fields = [];
                    foreach ($variants as $variant) {
                        $current = (float) ($variant->stockLevels->first()?->on_hand ?? 0);
                        $unit    = $variant->unit?->labelAr() ?? '';
                        $fields[] = Forms\Components\TextInput::make('qty_'.$variant->id)
                            ->label($variant->full_name.' ('.$variant->sku.') — '.$unit)
                            ->numeric()
                            ->minValue(0)
                            ->step(0.001)
                            ->required()
                            ->default($current);
                    }

                    $fields[] = Forms\Components\Textarea::make('note')
                        ->label('ملاحظة')
                        ->rows(2)
                        ->placeholder('سبب تعديل الكمية…');

                    return $fields;
                })
                ->action(function (array $data): void {
                    try {
                        $setter = app(SetVariantStockAction::class);
                        $note   = $data['note'] ?? 'تعديل كمية من بطاقة المنتج';

                        foreach ($this->record->variants as $variant) {
                            $key = 'qty_'.$variant->id;
                            if (! array_key_exists($key, $data)) {
                                continue;
                            }
                            $setter->execute(
                                variant: $variant,
                                targetQty: (float) $data[$key],
                                note: $note,
                            );
                        }

                        Notification::make()
                            ->title('تم تحديث الكميات')
                            ->success()
                            ->send();

                        $this->refreshFormData(['variants']);
                        $this->fillForm();
                    } catch (\Throwable $e) {
                        Notification::make()
                            ->title('فشل تحديث الكمية')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                }),

            Actions\DeleteAction::make()->label('حذف'),
        ];
    }

    protected function afterFill(): void
    {
        $this->hydrateStockQtyIntoForm();
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        $rawVariants = $this->form->getRawState()['variants'] ?? [];

        $record = parent::handleRecordUpdate($record, $data);

        $this->syncVariantStocks($rawVariants);

        return $record;
    }

    private function hydrateStockQtyIntoForm(): void
    {
        $warehouseId = Warehouse::query()->where('is_default', true)->value('id');
        $variants    = $this->data['variants'] ?? [];

        if (! is_array($variants) || ! $warehouseId) {
            return;
        }

        foreach ($variants as $key => $variant) {
            $variantId = $variant['id'] ?? null;
            $this->data['variants'][$key]['stock_qty'] = $variantId
                ? (float) (StockLevel::query()
                    ->where('variant_id', $variantId)
                    ->where('warehouse_id', $warehouseId)
                    ->value('on_hand') ?? 0)
                : 0.0;
        }
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
                targetQty: (float) $row['stock_qty'],
                note: 'تعديل كمية من تعديل المنتج',
            );
        }
    }
}
