<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\StockLevelResource\Pages;
use App\Domain\Inventory\Models\StockLevel;
use App\Filament\Clusters\InventoryCluster;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StockLevelResource extends Resource
{
    protected static ?string $model            = StockLevel::class;
    protected static ?string $cluster          = InventoryCluster::class;
    protected static ?string $navigationIcon   = 'heroicon-o-archive-box';
    protected static ?string $navigationLabel  = 'أرصدة المخزون';
    protected static ?string $modelLabel       = 'رصيد';
    protected static ?string $pluralModelLabel = 'أرصدة المخزون';
    protected static ?int    $navigationSort   = 1;

    /** شارة تحذير: عدد الأصناف تحت حد إعادة الطلب */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::lowStock()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'danger';
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('variant.product.name')
                    ->label('المنتج')->searchable()->sortable()->weight('medium'),

                Tables\Columns\TextColumn::make('variant.sku')
                    ->label('SKU')->searchable()->fontFamily('mono')->toggleable(),

                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('المخزن')->badge()->sortable(),

                Tables\Columns\TextColumn::make('on_hand')
                    ->label('الموجود')
                    ->formatStateUsing(fn ($state, StockLevel $record) =>
                        number_format((float) $state, 0) . ' ' . $record->variant->unit->labelAr())
                    ->sortable(),

                Tables\Columns\TextColumn::make('reserved')
                    ->label('المحجوز')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0))
                    ->color('warning'),

                Tables\Columns\TextColumn::make('available')
                    ->label('المتاح للبيع')
                    ->state(fn (StockLevel $r) => number_format($r->availableQty(), 0))
                    ->weight('bold')
                    ->color(fn (StockLevel $r) => match (true) {
                        $r->availableQty() <= 0        => 'danger',
                        $r->isBelowReorderPoint()      => 'warning',
                        default                        => 'success',
                    }),

                Tables\Columns\TextColumn::make('reorder_point')
                    ->label('حد الطلب')
                    ->formatStateUsing(fn ($state) => number_format((float) $state, 0))
                    ->toggleable(),

                // قيمة المخزون — للإدارة فقط
                Tables\Columns\TextColumn::make('value')
                    ->label('القيمة الدفترية')
                    ->visible(fn () => ! auth()->user()?->isCashier())
                    ->state(fn (StockLevel $r) =>
                        number_format((float) $r->on_hand * ($r->variant->getRawOriginal('cost_minor') ?? 0) / 100, 2) . ' ج.م'),

                Tables\Columns\TextColumn::make('last_movement_at')
                    ->label('آخر حركة')->since()->toggleable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('warehouse')
                    ->label('المخزن')->relationship('warehouse', 'name'),

                Tables\Filters\Filter::make('low_stock')
                    ->label('تحت حد إعادة الطلب')
                    ->query(fn (Builder $q) => $q->lowStock()),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label('نفد المخزون')
                    ->query(fn (Builder $q) => $q->outOfStock()),

                Tables\Filters\Filter::make('dead_stock')
                    ->label('مخزون راكد (٩٠ يومًا)')
                    ->query(fn (Builder $q) => $q->where('on_hand', '>', 0)
                        ->where('last_movement_at', '<', now()->subDays(90))),
            ])
            ->defaultSort('available');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['variant.product', 'warehouse']);
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListStockLevels::route('/')];
    }
}
