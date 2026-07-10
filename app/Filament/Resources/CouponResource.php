<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CouponResource\Pages;
use App\Domain\Pricing\Models\Coupon;
use App\Filament\Clusters\SalesCluster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model            = Coupon::class;
    protected static ?string $cluster          = SalesCluster::class;
    protected static ?string $navigationIcon   = 'heroicon-o-ticket';
    protected static ?string $navigationLabel  = 'الكوبونات';
    protected static ?string $modelLabel       = 'كوبون';
    protected static ?string $pluralModelLabel = 'الكوبونات';
    protected static ?int    $navigationSort   = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('code')
                    ->label('الكود')->required()->unique(ignoreRecord: true)
                    ->extraInputAttributes(['style' => 'text-transform:uppercase']),

                Forms\Components\Select::make('type')
                    ->label('نوع الخصم')->required()->live()
                    ->options(['percent' => 'نسبة مئوية', 'fixed' => 'مبلغ ثابت']),

                Forms\Components\TextInput::make('value')
                    ->label(fn (Forms\Get $get) => $get('type') === 'percent' ? 'النسبة (%)' : 'المبلغ (ج.م)')
                    ->numeric()->required(),

                Forms\Components\TextInput::make('max_discount_minor')
                    ->label('سقف الخصم (ج.م)')
                    ->numeric()->prefix('ج.م')
                    ->visible(fn (Forms\Get $get) => $get('type') === 'percent')
                    ->helperText('يحمي الهامش عند الطلبات الكبيرة')
                    ->formatStateUsing(fn ($state) => $state ? $state / 100 : null)
                    ->dehydrateStateUsing(fn ($state) => $state ? (int) round((float) $state * 100) : null),

                Forms\Components\TextInput::make('min_order_minor')
                    ->label('أقل قيمة للطلب (ج.م)')
                    ->numeric()->prefix('ج.م')->default(0)
                    ->formatStateUsing(fn ($state) => $state ? $state / 100 : 0)
                    ->dehydrateStateUsing(fn ($state) => (int) round((float) $state * 100)),

                Forms\Components\TextInput::make('usage_limit')
                    ->label('حد الاستخدام الكلي')->numeric()
                    ->helperText('فارغ = بلا حد'),

                Forms\Components\TextInput::make('usage_limit_per_customer')
                    ->label('حد الاستخدام لكل عميل')->numeric()->default(1),

                Forms\Components\Toggle::make('is_active')->label('مفعّل')->default(true),

                Forms\Components\DateTimePicker::make('starts_at')->label('يبدأ في'),
                Forms\Components\DateTimePicker::make('expires_at')->label('ينتهي في'),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('code')->label('الكود')->searchable()->copyable()->fontFamily('mono')->weight('bold'),

                Tables\Columns\TextColumn::make('value')
                    ->label('الخصم')
                    ->formatStateUsing(fn ($state, Coupon $record) => $record->type === 'percent'
                        ? "{$state}%"
                        : number_format($state / 100, 2) . ' ج.م'),

                Tables\Columns\TextColumn::make('min_order_minor')
                    ->label('أقل طلب')->formatStateUsing(fn ($state) => $state->format()),

                Tables\Columns\TextColumn::make('usage')
                    ->label('الاستخدام')
                    ->state(fn (Coupon $r) => $r->usage_limit
                        ? "{$r->used_count} / {$r->usage_limit}"
                        : (string) $r->used_count)
                    ->badge(),

                Tables\Columns\TextColumn::make('expires_at')->label('ينتهي')->date('Y-m-d')->placeholder('—'),
                Tables\Columns\IconColumn::make('is_active')->label('مفعّل')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make()->label('تعديل')]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCoupons::route('/'),
            'create' => Pages\CreateCoupon::route('/create'),
            'edit'   => Pages\EditCoupon::route('/{record}/edit'),
        ];
    }
}
