<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CustomerResource\Pages;
use App\Domain\Crm\Models\Customer;
use App\Filament\Clusters\SalesCluster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CustomerResource extends Resource
{
    protected static ?string $model            = Customer::class;
    protected static ?string $cluster          = SalesCluster::class;
    protected static ?string $navigationIcon   = 'heroicon-o-users';
    protected static ?string $navigationLabel  = 'العملاء';
    protected static ?string $modelLabel       = 'عميل';
    protected static ?string $pluralModelLabel = 'العملاء';
    protected static ?int    $navigationSort   = 2;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\Grid::make(2)->schema([
                Forms\Components\TextInput::make('name')->label('الاسم')->required(),
                Forms\Components\TextInput::make('phone')->label('الهاتف')->tel()->required()->unique(ignoreRecord: true),
                Forms\Components\TextInput::make('email')->label('البريد')->email(),
                Forms\Components\Toggle::make('is_active')->label('نشط')->default(true),
            ]),
            Forms\Components\Textarea::make('notes')->label('ملاحظات')->rows(2)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('الاسم')->searchable()->weight('medium'),
                Tables\Columns\TextColumn::make('phone')->label('الهاتف')->searchable()->copyable(),

                Tables\Columns\TextColumn::make('orders_count')
                    ->label('عدد الطلبات')->counts('orders')->badge()->sortable(),

                Tables\Columns\TextColumn::make('ltv')
                    ->label('القيمة الإجمالية')
                    ->state(fn (Customer $r) => $r->lifetimeValue()->format())
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('aov')
                    ->label('متوسط الطلب')
                    ->state(fn (Customer $r) => $r->averageOrderValue()->format()),

                Tables\Columns\IconColumn::make('repeat')
                    ->label('عميل متكرر')
                    ->state(fn (Customer $r) => $r->isRepeat())->boolean(),

                Tables\Columns\TextColumn::make('created_at')->label('العضوية')->date('Y-m-d')->toggleable(),
            ])
            ->actions([Tables\Actions\EditAction::make()->label('تعديل')])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCustomers::route('/'),
            'create' => Pages\CreateCustomer::route('/create'),
            'edit'   => Pages\EditCustomer::route('/{record}/edit'),
        ];
    }
}
