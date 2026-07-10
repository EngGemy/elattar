<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\SupplierResource\Pages;
use App\Domain\Purchasing\Models\Supplier;
use App\Filament\Clusters\PurchasingCluster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class SupplierResource extends Resource
{
    protected static ?string $model            = Supplier::class;
    protected static ?string $cluster          = PurchasingCluster::class;
    protected static ?string $navigationIcon   = 'heroicon-o-building-storefront';
    protected static ?string $navigationLabel  = 'الموردون';
    protected static ?string $modelLabel       = 'مورد';
    protected static ?string $pluralModelLabel = 'الموردون';
    protected static ?int    $navigationSort   = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('name')->label('اسم المورد')->required(),
                Forms\Components\TextInput::make('code')->label('الكود')->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('contact_name')->label('جهة الاتصال'),
                Forms\Components\TextInput::make('phone')->label('الهاتف')->tel(),
                Forms\Components\TextInput::make('email')->label('البريد')->email(),
                Forms\Components\TextInput::make('tax_number')->label('الرقم الضريبي'),

                Forms\Components\TextInput::make('payment_terms_days')
                    ->label('مهلة السداد (أيام)')->numeric()->default(0)
                    ->helperText('٠ = نقدًا عند الاستلام'),

                Forms\Components\Toggle::make('is_active')->label('نشط')->default(true),
            ]),
            Forms\Components\Textarea::make('address')->label('العنوان')->rows(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('المورد')->searchable()->weight('medium'),
                Tables\Columns\TextColumn::make('code')->label('الكود')->fontFamily('mono'),
                Tables\Columns\TextColumn::make('phone')->label('الهاتف'),

                Tables\Columns\TextColumn::make('payment_terms_days')
                    ->label('مهلة السداد')
                    ->formatStateUsing(fn ($state) => $state > 0 ? "{$state} يومًا" : 'نقدًا'),

                Tables\Columns\TextColumn::make('outstanding')
                    ->label('المستحق عليه')
                    ->state(fn (Supplier $r) => $r->outstandingBalance()->format())
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('purchase_orders_count')
                    ->label('أوامر الشراء')->counts('purchaseOrders')->badge(),

                Tables\Columns\IconColumn::make('is_active')->label('نشط')->boolean(),
            ])
            ->actions([Tables\Actions\EditAction::make()->label('تعديل')]);
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListSuppliers::route('/'),
            'create' => Pages\CreateSupplier::route('/create'),
            'edit'   => Pages\EditSupplier::route('/{record}/edit'),
        ];
    }
}
