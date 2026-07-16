<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Purchasing\Actions\ReceiveGoodsAction;
use App\Domain\Purchasing\Models\PurchaseOrder;
use App\Domain\Shared\ValueObjects\Money;
use App\Filament\Clusters\PurchasingCluster;
use App\Filament\Resources\PurchaseOrderResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class PurchaseOrderResource extends Resource
{
    protected static ?string $model            = PurchaseOrder::class;
    protected static ?string $cluster          = PurchasingCluster::class;
    protected static ?string $navigationIcon   = 'heroicon-o-clipboard-document-list';
    protected static ?string $navigationLabel  = 'أوامر الشراء';
    protected static ?string $modelLabel       = 'أمر شراء';
    protected static ?string $pluralModelLabel = 'أوامر الشراء';
    protected static ?int    $navigationSort   = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('بيانات الأمر')
                    ->description('اختر المورد والمخزن المستلم وتاريخ التسليم المتوقع')
                    ->icon('heroicon-o-document-text')
                    ->schema([
                        Forms\Components\Grid::make(3)->schema([
                            Forms\Components\Select::make('supplier_id')
                                ->label('المورد')
                                ->relationship('supplier', 'name')
                                ->searchable()
                                ->preload()
                                ->required()
                                ->placeholder('اختر المورد…'),

                            Forms\Components\Select::make('warehouse_id')
                                ->label('المخزن المستلم')
                                ->relationship('warehouse', 'name')
                                ->default(fn () => Warehouse::where('is_default', true)->value('id'))
                                ->required()
                                ->placeholder('اختر المخزن…'),

                            Forms\Components\DatePicker::make('expected_at')
                                ->label('تاريخ التسليم المتوقع')
                                ->native(false)
                                ->displayFormat('d/m/Y')
                                ->minDate(now()),
                        ]),

                        Forms\Components\Textarea::make('notes')
                            ->label('ملاحظات')
                            ->rows(2)
                            ->placeholder('ملاحظات داخلية على أمر الشراء…')
                            ->columnSpanFull(),
                    ]),

                Forms\Components\Section::make('الأصناف المطلوبة')
                    ->description('أضف الأصناف والكميات وتكلفة الوحدة بالجنيه')
                    ->icon('heroicon-o-shopping-cart')
                    ->schema([
                        Forms\Components\Repeater::make('lines')
                            ->label('')
                            ->relationship()
                            ->schema([
                                Forms\Components\Select::make('variant_id')
                                    ->label('الصنف')
                                    ->relationship('variant', 'sku')
                                    ->getOptionLabelFromRecordUsing(
                                        fn ($record) => $record->full_name . " ({$record->sku})"
                                    )
                                    ->searchable(['sku', 'barcode'])
                                    ->preload()
                                    ->required()
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('qty_ordered')
                                    ->label('الكمية المطلوبة')
                                    ->numeric()
                                    ->step(0.001)
                                    ->minValue(0.001)
                                    ->required()
                                    ->default(1)
                                    ->live(debounce: 300)
                                    ->helperText('عدّل الكمية هنا قبل الاستلام'),

                                Forms\Components\TextInput::make('qty_received')
                                    ->label('المستلم سابقًا')
                                    ->numeric()
                                    ->disabled()
                                    ->dehydrated(false)
                                    ->visibleOn('edit'),

                                Forms\Components\TextInput::make('unit_cost_minor')
                                    ->label('تكلفة الوحدة')
                                    ->numeric()
                                    ->required()
                                    ->prefix('ج.م')
                                    ->step(0.01)
                                    ->minValue(0)
                                    ->placeholder('0.00')
                                    ->live(debounce: 300)
                                    ->formatStateUsing(fn ($state) => $state ? $state / 100 : null)
                                    ->dehydrateStateUsing(fn ($state) => (int) round((float) $state * 100)),

                                Forms\Components\Placeholder::make('line_preview')
                                    ->label('إجمالي السطر')
                                    ->content(function (Get $get): string {
                                        $qty  = (float) ($get('qty_ordered') ?? 0);
                                        $cost = (float) ($get('unit_cost_minor') ?? 0);

                                        if ($qty <= 0 || $cost <= 0) {
                                            return '—';
                                        }

                                        return Money::ofMajor($qty * $cost)->format();
                                    }),
                            ])
                            ->columns(6)
                            ->defaultItems(1)
                            ->addActionLabel('إضافة صنف')
                            ->reorderable(false)
                            ->columnSpanFull()
                            ->itemLabel(
                                fn (array $state): ?string => isset($state['variant_id'])
                                    ? \App\Domain\Catalog\Models\ProductVariant::find($state['variant_id'])?->full_name
                                    : 'صنف جديد'
                            ),
                    ]),

                Forms\Components\Section::make('ملخص تقديري')
                    ->schema([
                        Forms\Components\Placeholder::make('order_total_preview')
                            ->label('إجمالي الأمر (تقديري)')
                            ->content(function (Get $get): string {
                                $lines = $get('lines') ?? [];
                                $total = collect($lines)->sum(function (array $line) {
                                    $qty  = (float) ($line['qty_ordered'] ?? 0);
                                    $cost = (float) ($line['unit_cost_minor'] ?? 0);

                                    return $qty * $cost;
                                });

                                return $total > 0
                                    ? Money::ofMajor($total)->format()
                                    : 'أضف أصنافاً لحساب الإجمالي';
                            }),
                    ])
                    ->visibleOn(['create', 'edit']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('رقم الأمر')
                    ->searchable()
                    ->fontFamily('mono')
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('supplier.name')
                    ->label('المورد')
                    ->searchable(),

                Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('المخزن')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => match ($state) {
                        'draft'              => 'مسودة',
                        'sent'               => 'مُرسل',
                        'partially_received' => 'استلام جزئي',
                        'received'           => 'مستلم بالكامل',
                        'cancelled'          => 'ملغي',
                        default              => $state,
                    })
                    ->color(fn ($state) => match ($state) {
                        'received'           => 'success',
                        'partially_received' => 'warning',
                        'cancelled'          => 'danger',
                        default              => 'gray',
                    }),

                Tables\Columns\TextColumn::make('total_minor')
                    ->label('الإجمالي')
                    ->state(fn (PurchaseOrder $record) => $record->totalCost()->format())
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('expected_at')
                    ->label('التسليم المتوقع')
                    ->date('Y-m-d'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('التاريخ')
                    ->date('Y-m-d')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')->label('الحالة')->options([
                    'draft'              => 'مسودة',
                    'sent'               => 'مُرسل',
                    'partially_received' => 'استلام جزئي',
                    'received'           => 'مستلم بالكامل',
                    'cancelled'          => 'ملغي',
                ]),
                Tables\Filters\SelectFilter::make('supplier')->label('المورد')->relationship('supplier', 'name'),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل')
                    ->visible(fn (PurchaseOrder $record) => in_array($record->status, ['draft', 'sent'], true)
                        && ! $record->lines->contains(fn ($l) => (float) $l->qty_received > 0)),

                Tables\Actions\Action::make('receive')
                    ->label('استلام بضاعة')
                    ->icon('heroicon-o-inbox-arrow-down')
                    ->color('primary')
                    ->visible(fn (PurchaseOrder $record) => ! in_array($record->status, ['received', 'cancelled'], true)
                        && $record->lines->contains(fn ($l) => $l->qtyPending() > 0))
                    ->fillForm(function (PurchaseOrder $record): array {
                        $lines = $record->lines
                            ->filter(fn ($line) => $line->qtyPending() > 0)
                            ->values()
                            ->map(fn ($line) => [
                                'po_line_id'       => $line->id,
                                'qty'              => $line->qtyPending(),
                                'unit_cost_minor'  => ((int) $line->getRawOriginal('unit_cost_minor')) / 100,
                            ])
                            ->all();

                        return [
                            'supplier_invoice_no' => null,
                            'lines'               => $lines,
                            'note'                => null,
                        ];
                    })
                    ->form(fn (PurchaseOrder $record) => [
                        Forms\Components\TextInput::make('supplier_invoice_no')
                            ->label('رقم فاتورة المورد'),

                        Forms\Components\Repeater::make('lines')
                            ->label('الكميات المستلمة — عدّل الكمية حسب الوارد الفعلي')
                            ->schema([
                                Forms\Components\Hidden::make('po_line_id')->required(),

                                Forms\Components\Placeholder::make('item_name')
                                    ->label('الصنف')
                                    ->content(function (Forms\Get $get) use ($record): string {
                                        $line = $record->lines->firstWhere('id', (int) $get('po_line_id'));

                                        if (! $line) {
                                            return '—';
                                        }

                                        return $line->variant->full_name
                                            . ' — متبقي: ' . number_format($line->qtyPending(), 0);
                                    })
                                    ->columnSpan(2),

                                Forms\Components\TextInput::make('qty')
                                    ->label('الكمية المستلمة')
                                    ->numeric()
                                    ->step(0.001)
                                    ->minValue(0.001)
                                    ->required()
                                    ->helperText('يمكن تقليل/تعديل الكمية قبل الاعتماد'),

                                Forms\Components\TextInput::make('unit_cost_minor')
                                    ->label('التكلفة الفعلية (ج.م)')
                                    ->numeric()
                                    ->prefix('ج.م')
                                    ->step(0.01)
                                    ->helperText('اتركها كما هي لاستخدام تكلفة الأمر')
                                    ->dehydrateStateUsing(fn ($state) => $state !== null && $state !== ''
                                        ? (int) round((float) $state * 100)
                                        : null),
                            ])
                            ->columns(4)
                            ->addable(false)
                            ->deletable(false)
                            ->reorderable(false)
                            ->columnSpanFull(),

                        Forms\Components\Textarea::make('note')->label('ملاحظات')->rows(2),
                    ])
                    ->action(function (PurchaseOrder $record, array $data, ReceiveGoodsAction $action) {
                        try {
                            $lines = array_values(array_filter(
                                $data['lines'] ?? [],
                                fn ($line) => ! empty($line['po_line_id']) && (float) ($line['qty'] ?? 0) > 0
                            ));

                            if ($lines === []) {
                                throw new \RuntimeException('حدد كمية مستلمة لصنف واحد على الأقل.');
                            }

                            $receipt = $action->execute(
                                po: $record,
                                lines: $lines,
                                supplierInvoiceNo: $data['supplier_invoice_no'] ?? null,
                                note: $data['note'] ?? null,
                            );

                            Notification::make()
                                ->title("تم الاستلام — إذن {$receipt->number}")
                                ->body('تم تحديث كميات المخزون وإعادة حساب متوسط التكلفة.')
                                ->success()
                                ->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('فشل الاستلام')->body($e->getMessage())->danger()->send();
                        }
                    }),

                Tables\Actions\Action::make('send')
                    ->label('إرسال للمورد')
                    ->icon('heroicon-o-paper-airplane')
                    ->color('info')
                    ->visible(fn (PurchaseOrder $record) => $record->status === 'draft')
                    ->requiresConfirmation()
                    ->action(function (PurchaseOrder $record) {
                        $record->update(['status' => 'sent']);
                        Notification::make()->title('تم تعليم الأمر كمُرسل')->success()->send();
                    }),

                Tables\Actions\Action::make('cancel')
                    ->label('إلغاء')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn (PurchaseOrder $record) => in_array($record->status, ['draft', 'sent'], true))
                    ->requiresConfirmation()
                    ->action(fn (PurchaseOrder $record) => $record->update(['status' => 'cancelled'])),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['supplier', 'warehouse', 'lines.variant.product']);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPurchaseOrders::route('/'),
            'create' => Pages\CreatePurchaseOrder::route('/create'),
            'edit'   => Pages\EditPurchaseOrder::route('/{record}/edit'),
        ];
    }
}
