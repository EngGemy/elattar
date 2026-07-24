<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Support\FilamentMoney;
use App\Filament\Resources\ProductResource\Pages;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Shared\Enums\ProductStatus;
use App\Domain\Shared\Enums\ProductType;
use App\Domain\Shared\Enums\UnitOfMeasure;
use App\Filament\Clusters\CatalogCluster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class ProductResource extends Resource
{
    protected static ?string $model           = Product::class;
    protected static ?string $cluster         = CatalogCluster::class;
    protected static ?string $navigationIcon  = 'heroicon-o-cube';
    protected static ?string $navigationLabel = 'المنتجات';
    protected static ?string $modelLabel      = 'منتج';
    protected static ?string $pluralModelLabel = 'المنتجات';
    protected static ?int    $navigationSort  = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make()->columnSpanFull()->tabs([

                // ── البيانات الأساسية
                Forms\Components\Tabs\Tab::make('البيانات الأساسية')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم المنتج')->required()->maxLength(255)
                            ->live(onBlur: true),

                        Forms\Components\TextInput::make('sku_root')
                            ->label('كود المنتج (SKU)')->required()->unique(ignoreRecord: true),

                        Forms\Components\Select::make('category_id')
                            ->label('التصنيف')
                            ->relationship('category', 'name')
                            ->searchable()->preload()->required(),

                        Forms\Components\Select::make('tax_class_id')
                            ->label('فئة الضريبة')
                            ->relationship('taxClass', 'name')
                            ->default(fn () => \App\Domain\Catalog\Models\TaxClass::default()?->id),

                        Forms\Components\Select::make('type')
                            ->label('نوع البيع')
                            ->options(ProductType::class)
                            ->default(ProductType::Simple)
                            ->required()
                            ->helperText('«بالوزن» للبهارات والحبوب — «بسيط» للعبوات والقطع'),

                        Forms\Components\Select::make('status')
                            ->label('الحالة')
                            ->options(ProductStatus::class)
                            ->default(ProductStatus::Draft)->required(),
                    ]),

                    Forms\Components\Textarea::make('short_description')
                        ->label('وصف مختصر')->rows(2)->columnSpanFull(),

                    Forms\Components\RichEditor::make('long_description')
                        ->label('الوصف التفصيلي')->columnSpanFull(),

                    Forms\Components\Grid::make(3)->schema([
                        Forms\Components\Toggle::make('is_featured')->label('منتج مميّز'),
                        Forms\Components\TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
                    ]),
                ]),

                // ── الصور
                Forms\Components\Tabs\Tab::make('الصور')->schema([
                    Forms\Components\SpatieMediaLibraryFileUpload::make('main')
                        ->label('الصورة الرئيسية')
                        ->collection('main')->image()->imageEditor(),

                    Forms\Components\SpatieMediaLibraryFileUpload::make('gallery')
                        ->label('معرض الصور')
                        ->collection('gallery')->multiple()->reorderable()->image(),
                ]),

                // ── المتغيّرات والأسعار
                Forms\Components\Tabs\Tab::make('الأسعار والمتغيّرات')->schema([
                    Forms\Components\Repeater::make('variants')
                        ->label('المتغيّرات')
                        ->relationship()
                        ->mutateRelationshipDataBeforeSaveUsing(function (array $data): array {
                            unset($data['stock_qty']);

                            return $data;
                        })
                        ->schema([
                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('sku')
                                    ->label('SKU')->required()->unique(ignoreRecord: true),

                                Forms\Components\TextInput::make('barcode')
                                    ->label('الباركود')
                                    ->helperText('يُمسح ضوئيًا في نقطة البيع'),

                                Forms\Components\Select::make('unit')
                                    ->label('وحدة القياس')
                                    ->options(UnitOfMeasure::class)
                                    ->default(UnitOfMeasure::Piece)
                                    ->required()
                                    ->live()
                                    ->afterStateUpdated(function ($state, Forms\Set $set): void {
                                        $unit = $state instanceof UnitOfMeasure
                                            ? $state
                                            : (is_string($state) ? UnitOfMeasure::tryFrom($state) : null);

                                        if ($unit) {
                                            $set('step', $unit->defaultStep());
                                        }
                                    }),
                            ]),

                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('price_minor')
                                    ->label(fn (Forms\Get $get) =>
                                        in_array(self::unitValue($get('unit')), ['gram', 'ml'], true)
                                            ? 'سعر الكيلو/اللتر (ج.م)'
                                            : 'سعر البيع (ج.م)')
                                    ->numeric()->required()->prefix('ج.م')
                                    ->formatStateUsing(fn ($state) => FilamentMoney::toMajor($state))
                                    ->dehydrateStateUsing(fn ($state) => FilamentMoney::toMinor($state) ?? 0)
                                    ->live(onBlur: true),

                                Forms\Components\TextInput::make('cost_minor')
                                    ->label('التكلفة (ج.م)')
                                    ->numeric()->prefix('ج.م')
                                    // ⚠ الكاشير لا يرى التكلفة
                                    ->visible(fn () => ! auth()->user()?->isCashier())
                                    ->formatStateUsing(fn ($state) => FilamentMoney::toMajor($state))
                                    ->dehydrateStateUsing(fn ($state) => FilamentMoney::toMinor($state) ?? 0)
                                    ->helperText('يُعاد حسابها تلقائيًا عند استلام بضاعة')
                                    ->live(onBlur: true),

                                Forms\Components\TextInput::make('step')
                                    ->label('خطوة الكمية (أقل زيادة)')
                                    ->numeric()->step(0.001)->required()->default(1)
                                    ->helperText('١ للبهارات بالجرام — ١ للقطع — يمكنك 25 أو 50 لو حابب'),
                            ]),

                            Forms\Components\Grid::make(3)->schema([
                                Forms\Components\TextInput::make('unit_label')
                                    ->label('وصف العبوة')
                                    ->placeholder('برطمان 500جم / كيس / لتر'),

                                Forms\Components\TextInput::make('weight_grams')
                                    ->label('الوزن (جم)')->numeric()
                                    ->helperText('لحساب أجرة الشحن'),

                                Forms\Components\Toggle::make('is_default')
                                    ->label('المتغيّر الافتراضي')->default(true),
                            ]),

                            Forms\Components\TextInput::make('stock_qty')
                                ->label(fn (Forms\Get $get) =>
                                    in_array(self::unitValue($get('unit')), ['gram', 'ml'], true)
                                        ? 'الكمية بالمخزن (جم / مل)'
                                        : 'الكمية بالمخزن')
                                ->numeric()
                                ->minValue(0)
                                ->step(0.001)
                                ->default(0)
                                ->dehydrated() // يُحفظ في حالة الفورم ثم يُزامن للمخزون بعد الحفظ
                                ->helperText('غيّر الكمية واحفظ — أو استخدم زر «تعديل الكمية» أعلى الصفحة')
                                ->visible(fn () => ! auth()->user()?->isCashier())
                                ->columnSpanFull(),

                            // مؤشر هامش الربح — يُحسب لحظيًا
                            Forms\Components\Placeholder::make('margin')
                                ->label('هامش الربح المتوقع')
                                ->visible(fn () => ! auth()->user()?->isCashier())
                                ->content(function (Forms\Get $get): string {
                                    $price = self::moneyMajorFromForm($get('price_minor'));
                                    $cost  = self::moneyMajorFromForm($get('cost_minor'));

                                    if ($price <= 0) {
                                        return '—';
                                    }

                                    $gp = round(($price - $cost) / $price * 100, 1);

                                    return "{$gp}%  (ربح " . number_format($price - $cost, 2) . ' ج.م)';
                                }),
                        ])
                        ->itemLabel(fn (array $state): ?string => $state['sku'] ?? null)
                        ->defaultItems(1)
                        ->minItems(1)
                        ->addActionLabel('إضافة متغيّر')
                        ->reorderable(false)
                        ->collapsible()
                        ->columnSpanFull(),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('main')
                    ->label('')->collection('main')->conversion('thumb')->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('المنتج')->searchable()->sortable()->weight('medium'),

                Tables\Columns\TextColumn::make('sku_root')
                    ->label('الكود')->searchable()->toggleable()->fontFamily('mono'),

                Tables\Columns\TextColumn::make('category.name')
                    ->label('التصنيف')->badge()->sortable(),

                Tables\Columns\TextColumn::make('type')
                    ->label('النوع')->badge(),

                Tables\Columns\TextColumn::make('defaultVariant.price_minor')
                    ->label('السعر')
                    ->formatStateUsing(fn ($state) => $state?->format() ?? '—')
                    ->sortable(),

                // هامش الربح — مخفي عن الكاشير
                Tables\Columns\TextColumn::make('gp')
                    ->label('هامش الربح')
                    ->visible(fn () => ! auth()->user()?->isCashier())
                    ->state(fn (Product $r) => $r->defaultVariant
                        ? $r->defaultVariant->grossMarginPercent() . '%'
                        : '—')
                    ->color(fn ($state) => (float) $state < 20 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('stock')
                    ->label('المتاح')
                    ->state(fn (Product $r) => number_format($r->totalAvailable(), 0))
                    ->color(fn (Product $r) => $r->isOutOfStock() ? 'danger' : 'gray'),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')->badge(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('category')
                    ->label('التصنيف')->relationship('category', 'name')->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')->options(ProductStatus::class),

                Tables\Filters\Filter::make('out_of_stock')
                    ->label('نفد المخزون')
                    ->query(fn (Builder $q) => $q->whereDoesntHave('variants.stockLevels',
                        fn ($sq) => $sq->whereRaw('on_hand - reserved > 0'))),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make()->label('حذف المحدد'),
                ]),
            ])
            ->defaultSort('sort_order');
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['category', 'defaultVariant', 'variants']);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit'   => Pages\EditProduct::route('/{record}/edit'),
        ];
    }

    private static function unitValue(mixed $unit): ?string
    {
        if ($unit instanceof UnitOfMeasure) {
            return $unit->value;
        }

        return is_string($unit) ? $unit : null;
    }

    /** السعر في الفورم: Money عند التعبئة، قروش (int) بعد dehydrate، جنيهات أثناء الكتابة */
    private static function moneyMajorFromForm(mixed $state): float
    {
        if ($state === null || $state === '') {
            return 0.0;
        }

        if ($state instanceof \App\Domain\Shared\ValueObjects\Money) {
            return round($state->minor / 100, 2);
        }

        if (is_int($state)) {
            return round($state / 100, 2);
        }

        return round((float) $state, 2);
    }
}
