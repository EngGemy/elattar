<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Catalog\Models\ProductVariant;
use App\Filament\Resources\StockAdjustmentResource\Pages;
use App\Domain\Inventory\Actions\AdjustStockAction;
use App\Domain\Inventory\Models\StockAdjustment;
use App\Domain\Shared\Enums\AdjustmentReason;
use App\Filament\Clusters\InventoryCluster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class StockAdjustmentResource extends Resource
{
    protected static ?string $model            = StockAdjustment::class;
    protected static ?string $cluster          = InventoryCluster::class;
    protected static ?string $navigationIcon   = 'heroicon-o-adjustments-horizontal';
    protected static ?string $navigationLabel  = 'تسويات الجرد';
    protected static ?string $modelLabel       = 'تسوية';
    protected static ?string $pluralModelLabel = 'تسويات الجرد';
    protected static ?int    $navigationSort   = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(3)->schema([
                Forms\Components\Select::make('warehouse_id')
                    ->label('المخزن')->relationship('warehouse', 'name')->required(),

                Forms\Components\Select::make('reason')
                    ->label('السبب')->options(AdjustmentReason::class)->required(),

                Forms\Components\TextInput::make('reference_doc')->label('المستند المرجعي'),
            ]),

            Forms\Components\Textarea::make('note')->label('ملاحظات')->rows(2)->columnSpanFull(),

            Forms\Components\Repeater::make('lines')
                ->label('الأصناف')
                ->relationship()
                ->schema([
                    Forms\Components\Select::make('variant_id')
                        ->label('الصنف')
                        ->relationship('variant', 'sku')
                        ->getOptionLabelFromRecordUsing(fn (ProductVariant $record) => $record->full_name . " ({$record->sku})")
                        ->searchable()->preload()->required()->columnSpan(2),

                    Forms\Components\TextInput::make('qty_delta')
                        ->label('الفرق (+ / −)')
                        ->numeric()->step(0.001)->required()
                        ->helperText('موجب = زيادة، سالب = عجز'),
                ])
                ->columns(3)
                ->defaultItems(1)
                ->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')->label('رقم التسوية')->fontFamily('mono')->weight('bold'),
                Tables\Columns\TextColumn::make('warehouse.name')->label('المخزن')->badge(),
                Tables\Columns\TextColumn::make('reason')->label('السبب')->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft'    => 'مسودة',
                        'approved' => 'معتمدة',
                        'rejected' => 'مرفوضة',
                        default    => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'approved' => 'success',
                        'rejected' => 'danger',
                        default    => 'gray',
                    }),

                Tables\Columns\TextColumn::make('lines_count')->label('عدد الأصناف')->counts('lines')->badge(),
                Tables\Columns\TextColumn::make('creator.name')->label('أنشأها')->toggleable(),
                Tables\Columns\TextColumn::make('created_at')->label('التاريخ')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل')
                    ->visible(fn (StockAdjustment $r) => $r->status === 'draft'),

                // الاعتماد وحده يحرّك المخزون — المسودة لا أثر لها
                Tables\Actions\Action::make('approve')
                    ->label('اعتماد')->icon('heroicon-o-check-circle')->color('success')
                    ->visible(fn (StockAdjustment $r) => $r->status === 'draft')
                    ->requiresConfirmation()
                    ->modalDescription('سيتم تعديل أرصدة المخزون فورًا. لا يمكن التراجع.')
                    ->action(function (StockAdjustment $r, AdjustStockAction $action) {
                        try {
                            $action->approve($r);
                            Notification::make()->title('تم اعتماد التسوية وتحديث المخزون')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('فشل الاعتماد')->body($e->getMessage())->danger()->send();
                        }
                    }),

                Tables\Actions\Action::make('reject')
                    ->label('رفض')->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn (StockAdjustment $r) => $r->status === 'draft')
                    ->requiresConfirmation()
                    ->action(fn (StockAdjustment $r) => $r->update(['status' => 'rejected'])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListStockAdjustments::route('/'),
            'create' => Pages\CreateStockAdjustment::route('/create'),
            'edit'   => Pages\EditStockAdjustment::route('/{record}/edit'),
        ];
    }
}
