<?php

declare(strict_types=1);

namespace App\Filament\Widgets;

use App\Domain\Inventory\Models\StockLevel;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

/** الأصناف التي تحتاج إعادة طلب — إجراء فوري */
class LowStockTable extends BaseWidget
{
    protected static ?string $heading = 'أصناف تحتاج إعادة طلب';
    protected static ?int    $sort    = 3;
    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return ! auth()->user()?->isCashier();
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(StockLevel::query()->lowStock()->with(['variant.product', 'warehouse']))
            ->columns([
                Tables\Columns\TextColumn::make('variant.product.name')->label('المنتج')->weight('medium'),
                Tables\Columns\TextColumn::make('warehouse.name')->label('المخزن')->badge(),

                Tables\Columns\TextColumn::make('available')
                    ->label('المتاح')
                    ->state(fn (StockLevel $record) => number_format($record->availableQty(), 0) . ' ' . $record->variant->unit->labelAr())
                    ->color('danger')->weight('bold'),

                Tables\Columns\TextColumn::make('reorder_point')
                    ->label('حد الطلب')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0)),

                Tables\Columns\TextColumn::make('reorder_qty')
                    ->label('الكمية المقترحة')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0)),

                Tables\Columns\TextColumn::make('last_movement_at')->label('آخر حركة')->since(),
            ])
            ->paginated([5, 10]);
    }
}
