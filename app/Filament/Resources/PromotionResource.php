<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Pricing\Enums\PromotionDiscountType;
use App\Domain\Pricing\Enums\PromotionScope;
use App\Domain\Pricing\Models\Promotion;
use App\Domain\Shared\ValueObjects\Money;
use App\Filament\Clusters\SalesCluster;
use App\Filament\Resources\PromotionResource\Pages;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Str;

class PromotionResource extends Resource
{
    protected static ?string $model            = Promotion::class;
    protected static ?string $cluster          = SalesCluster::class;
    protected static ?string $navigationIcon   = 'heroicon-o-tag';
    protected static ?string $navigationLabel  = 'العروض';
    protected static ?string $modelLabel       = 'عرض';
    protected static ?string $pluralModelLabel = 'العروض';
    protected static ?int    $navigationSort   = 2;

    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::active()->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'success';
    }

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Tabs::make()->columnSpanFull()->tabs([

                Forms\Components\Tabs\Tab::make('بيانات العرض')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('اسم العرض')->required()->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn ($state, Forms\Set $set, ?Promotion $record) =>
                                $record?->exists ? null : $set('slug', Str::slug($state ?? ''))),

                        Forms\Components\TextInput::make('slug')
                            ->label('الرابط')->required()->unique(ignoreRecord: true),

                        Forms\Components\Textarea::make('description')
                            ->label('الوصف')->rows(3)->columnSpanFull(),

                        Forms\Components\SpatieMediaLibraryFileUpload::make('banner')
                            ->label('صورة البانر')
                            ->collection('banner')->image()->imageEditor()->columnSpanFull(),

                        Forms\Components\TextInput::make('badge_text')
                            ->label('نص الشارة')
                            ->placeholder('خصم 20%')
                            ->maxLength(60),
                    ]),
                ]),

                Forms\Components\Tabs\Tab::make('الخصم')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\Select::make('discount_type')
                            ->label('نوع الخصم')->required()->live()
                            ->options(PromotionDiscountType::class)
                            ->default(PromotionDiscountType::Percent),

                        Forms\Components\TextInput::make('discount_value')
                            ->label(fn (Get $get) => match ($get('discount_type')) {
                                PromotionDiscountType::Percent->value => 'النسبة (%)',
                                PromotionDiscountType::FixedPrice->value => 'السعر الثابت (ج.م)',
                                default => 'مبلغ الخصم (ج.م)',
                            })
                            ->numeric()->required()
                            ->formatStateUsing(function ($state, Get $get) {
                                if (! $state) {
                                    return null;
                                }

                                return $get('discount_type') === PromotionDiscountType::Percent->value
                                    ? $state / 100
                                    : $state / 100;
                            })
                            ->dehydrateStateUsing(function ($state, Get $get) {
                                if ($state === null || $state === '') {
                                    return null;
                                }

                                return $get('discount_type') === PromotionDiscountType::Percent->value
                                    ? (int) round((float) $state * 100)
                                    : (int) round((float) $state * 100);
                            }),

                        Forms\Components\TextInput::make('max_discount_minor')
                            ->label('سقف الخصم (ج.م)')
                            ->numeric()->prefix('ج.م')
                            ->visible(fn (Get $get) => $get('discount_type') === PromotionDiscountType::Percent->value)
                            ->helperText('يحمي الهامش عند الطلبات الكبيرة')
                            ->formatStateUsing(fn ($state) => $state ? $state / 100 : null)
                            ->dehydrateStateUsing(fn ($state) => $state ? (int) round((float) $state * 100) : null),
                    ]),

                    Forms\Components\Placeholder::make('discount_preview')
                        ->label('معاينة')
                        ->content(function (Get $get): string {
                            $type  = $get('discount_type');
                            $value = $get('discount_value');

                            if (! $type || $value === null || $value === '') {
                                return 'أدخل بيانات الخصم لمعاينة النتيجة';
                            }

                            $original = Money::ofMinor(32000);
                            $promo    = new Promotion([
                                'discount_type'  => $type,
                                'discount_value' => $type === PromotionDiscountType::Percent->value
                                    ? (int) round((float) $value * 100)
                                    : (int) round((float) $value * 100),
                                'max_discount_minor' => $get('max_discount_minor')
                                    ? (int) round((float) $get('max_discount_minor') * 100)
                                    : null,
                            ]);

                            $discounted = $promo->applyTo($original);
                            $saved      = $original->minus($discounted);

                            return "منتج بـ {$original->format()} → {$discounted->format()} (وفّر {$saved->format()})";
                        }),
                ]),

                Forms\Components\Tabs\Tab::make('النطاق')->schema([
                    Forms\Components\Select::make('scope')
                        ->label('نطاق العرض')->required()->live()
                        ->options(PromotionScope::class)
                        ->default(PromotionScope::Product)
                        ->helperText('ابدأ بـ «منتجات محددة» — تجنّب تحميل كل المتغيّرات دفعة واحدة'),

                    Forms\Components\Select::make('categories')
                        ->label('التصنيفات')
                        ->relationship('categories', 'name')
                        ->multiple()->searchable()
                        ->visible(fn (Get $get) => $get('scope') === PromotionScope::Category->value)
                        ->dehydrated(fn (Get $get) => $get('scope') === PromotionScope::Category->value),

                    Forms\Components\Select::make('products')
                        ->label('المنتجات')
                        ->relationship('products', 'name')
                        ->multiple()->searchable()
                        ->getSearchResultsUsing(function (string $search): array {
                            return \App\Domain\Catalog\Models\Product::query()
                                ->where(function ($q) use ($search) {
                                    $q->where('name', 'like', "%{$search}%")
                                        ->orWhere('sku_root', 'like', "%{$search}%");
                                })
                                ->orderBy('name')
                                ->limit(50)
                                ->pluck('name', 'id')
                                ->all();
                        })
                        ->getOptionLabelsUsing(fn (array $values): array =>
                            \App\Domain\Catalog\Models\Product::query()
                                ->whereIn('id', $values)
                                ->pluck('name', 'id')
                                ->all())
                        ->visible(fn (Get $get) => $get('scope') === PromotionScope::Product->value)
                        ->dehydrated(fn (Get $get) => $get('scope') === PromotionScope::Product->value),

                    Forms\Components\Select::make('variants')
                        ->label('المتغيّرات')
                        ->relationship(
                            'variants',
                            'sku',
                            fn ($query) => $query->with('product')->orderBy('sku'),
                        )
                        ->getOptionLabelFromRecordUsing(fn (ProductVariant $record) =>
                            trim(($record->product?->name ?? '').' — '.$record->sku, ' —'))
                        ->getSearchResultsUsing(function (string $search): array {
                            return ProductVariant::query()
                                ->with('product')
                                ->where(function ($q) use ($search) {
                                    $q->where('sku', 'like', "%{$search}%")
                                        ->orWhereHas('product', fn ($pq) => $pq->where('name', 'like', "%{$search}%"));
                                })
                                ->orderBy('sku')
                                ->limit(40)
                                ->get()
                                ->mapWithKeys(fn (ProductVariant $v) => [
                                    $v->id => trim(($v->product?->name ?? '').' — '.$v->sku, ' —'),
                                ])
                                ->all();
                        })
                        ->getOptionLabelsUsing(function (array $values): array {
                            return ProductVariant::query()
                                ->with('product')
                                ->whereIn('id', $values)
                                ->get()
                                ->mapWithKeys(fn (ProductVariant $v) => [
                                    $v->id => trim(($v->product?->name ?? '').' — '.$v->sku, ' —'),
                                ])
                                ->all();
                        })
                        ->multiple()->searchable()
                        ->visible(fn (Get $get) => $get('scope') === PromotionScope::Variant->value)
                        ->dehydrated(fn (Get $get) => $get('scope') === PromotionScope::Variant->value),
                ]),

                Forms\Components\Tabs\Tab::make('الجدولة')->schema([
                    Forms\Components\Grid::make(2)->schema([
                        Forms\Components\DateTimePicker::make('starts_at')->label('يبدأ في'),
                        Forms\Components\DateTimePicker::make('ends_at')->label('ينتهي في'),

                        Forms\Components\TextInput::make('priority')
                            ->label('الأولوية')->numeric()->default(0)
                            ->helperText('الأعلى يفوز عند تعارض عرضين'),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('الترتيب')->numeric()->default(0),

                        Forms\Components\Toggle::make('is_active')->label('مفعّل')->default(true),
                        Forms\Components\Toggle::make('is_featured')->label('مميّز في الرئيسية')->default(false),
                    ]),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\SpatieMediaLibraryImageColumn::make('banner')
                    ->label('البانر')->collection('banner')->circular(),

                Tables\Columns\TextColumn::make('name')
                    ->label('الاسم')->searchable()->weight('bold'),

                Tables\Columns\TextColumn::make('discount')
                    ->label('الخصم')
                    ->state(fn (Promotion $r) => $r->discountLabel()),

                Tables\Columns\TextColumn::make('scope')
                    ->label('النطاق')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state instanceof PromotionScope ? $state->getLabel() : $state),

                Tables\Columns\TextColumn::make('targets_count')
                    ->label('الأهداف')
                    ->counts('targets')
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->state(fn (Promotion $r) => $r->statusLabel())
                    ->color(fn (Promotion $r) => $r->statusColor()),

                Tables\Columns\TextColumn::make('days_remaining')
                    ->label('المتبقي')
                    ->state(fn (Promotion $r) => $r->daysRemaining() !== null
                        ? $r->daysRemaining() . ' أيام متبقية'
                        : '—'),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label('مميّز')->boolean(),
            ])
            ->defaultSort('sort_order')
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\Action::make('toggle')
                    ->label(fn (Promotion $r) => $r->is_active ? 'إيقاف' : 'تفعيل')
                    ->icon(fn (Promotion $r) => $r->is_active ? 'heroicon-o-pause' : 'heroicon-o-play')
                    ->action(fn (Promotion $r) => $r->update(['is_active' => ! $r->is_active])),
                Tables\Actions\Action::make('duplicate')
                    ->label('نسخ')
                    ->icon('heroicon-o-document-duplicate')
                    ->action(function (Promotion $record) {
                        $copy = $record->replicate(['slug']);
                        $copy->name = $record->name . ' (نسخة)';
                        $copy->slug = $record->slug . '-copy-' . now()->format('His');
                        $copy->is_active = false;
                        $copy->save();

                        foreach ($record->targets as $target) {
                            $copy->targets()->create([
                                'target_type' => $target->target_type,
                                'target_id'   => $target->target_id,
                            ]);
                        }

                        if ($media = $record->getFirstMedia('banner')) {
                            $media->copy($copy, 'banner');
                        }

                        Notification::make()
                            ->title('تم نسخ العرض')
                            ->success()
                            ->send();
                    }),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListPromotions::route('/'),
            'create' => Pages\CreatePromotion::route('/create'),
            'edit'   => Pages\EditPromotion::route('/{record}/edit'),
        ];
    }
}
