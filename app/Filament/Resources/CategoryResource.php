<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\CategoryResource\Pages;
use App\Domain\Catalog\Models\Category;
use App\Filament\Clusters\CatalogCluster;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;

class CategoryResource extends Resource
{
    protected static ?string $model            = Category::class;
    protected static ?string $cluster          = CatalogCluster::class;
    protected static ?string $navigationIcon   = 'heroicon-o-folder';
    protected static ?string $navigationLabel  = 'التصنيفات';
    protected static ?string $modelLabel       = 'تصنيف';
    protected static ?string $pluralModelLabel = 'التصنيفات';
    protected static ?int    $navigationSort   = 1;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('name')->label('الاسم')->required(),

            Forms\Components\Select::make('parent_id')
                ->label('التصنيف الأب')
                ->relationship('parent', 'name')
                ->searchable()->preload()
                ->helperText('اتركه فارغًا للتصنيف الرئيسي'),

            Forms\Components\TextInput::make('icon')
                ->label('الأيقونة')->placeholder('heroicon-o-fire'),

            Forms\Components\Textarea::make('description')->label('الوصف')->rows(2),

            Forms\Components\Grid::make(3)->schema([
                Forms\Components\TextInput::make('sort_order')->label('الترتيب')->numeric()->default(0),
                Forms\Components\Toggle::make('is_featured')->label('مميّز'),
                Forms\Components\Toggle::make('is_active')->label('نشط')->default(true),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('التصنيف')->searchable()
                    ->formatStateUsing(fn (Category $r) => str_repeat('— ', $r->depth) . $r->name),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('عدد المنتجات')->counts('products')->badge(),

                Tables\Columns\IconColumn::make('is_featured')->label('مميّز')->boolean(),
                Tables\Columns\IconColumn::make('is_active')->label('نشط')->boolean(),
                Tables\Columns\TextColumn::make('sort_order')->label('الترتيب')->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->label('تعديل'),
                Tables\Actions\DeleteAction::make()->label('حذف'),
            ])
            ->defaultSort('_lft');
    }

    public static function getPages(): array
    {
        return [
            'index'  => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit'   => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
