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

    /** لقطة كميات الفورم قبل أن يفرّغها Filament بعد حفظ العلاقات */
    private ?array $pendingVariantStocks = null;

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

    /**
     * يُستدعى داخل getState قبل saveRelationships — ما زال stock_qty موجودًا في الحالة.
     */
    protected function beforeSave(): void
    {
        $this->pendingVariantStocks = $this->form->getRawState()['variants'] ?? [];
    }

    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        // لا نزامن المخزون هنا — getRawState يكون قد فُرغ بعد تحميل العلاقات
        return parent::handleRecordUpdate($record, $data);
    }

    protected function afterSave(): void
    {
        if ($this->pendingVariantStocks === null) {
            return;
        }

        $this->syncVariantStocks($this->pendingVariantStocks);
        $this->pendingVariantStocks = null;
    }

    /** @param  array<string, mixed>  $rows */
    private function syncVariantStocks(array $rows): void
    {
        $setter = app(SetVariantStockAction::class);

        foreach ($rows as $row) {
            if (! is_array($row) || ! array_key_exists('stock_qty', $row)) {
                continue;
            }

            // null/فارغ = المستخدم لم يغيّر الكمية أو الحقل اختفى — لا تمس المخزون
            if ($row['stock_qty'] === null || $row['stock_qty'] === '') {
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
                note: 'تعديل كمية من تعديل المنتج',
            );
        }
    }
}
