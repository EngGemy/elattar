<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\RegisterSessionResource\Pages;
use App\Domain\Pos\Models\RegisterSession;
use App\Filament\Clusters\ReportsCluster;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

/** سجل الشيفتات — الرقم الأهم هو variance (الفرق) */
class RegisterSessionResource extends Resource
{
    protected static ?string $model            = RegisterSession::class;
    protected static ?string $cluster          = ReportsCluster::class;
    protected static ?string $navigationIcon   = 'heroicon-o-banknotes';
    protected static ?string $navigationLabel  = 'شيفتات الكاشير';
    protected static ?string $modelLabel       = 'شيفت';
    protected static ?string $pluralModelLabel = 'شيفتات الكاشير';
    protected static ?int    $navigationSort   = 2;

    public static function canAccess(): bool
    {
        return ! auth()->user()?->isCashier();
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')->label('الكاشير')->searchable()->weight('medium'),
                Tables\Columns\TextColumn::make('register.name')->label('الصندوق')->badge(),

                Tables\Columns\TextColumn::make('opened_at')->label('الفتح')->dateTime('Y-m-d H:i')->sortable(),
                Tables\Columns\TextColumn::make('closed_at')->label('الإقفال')->dateTime('Y-m-d H:i')->placeholder('مفتوح'),

                Tables\Columns\TextColumn::make('orders_count')->label('الطلبات')->badge(),

                Tables\Columns\TextColumn::make('cash_sales_minor')
                    ->label('مبيعات نقدية')->formatStateUsing(fn ($state) => $state->format()),

                Tables\Columns\TextColumn::make('card_sales_minor')
                    ->label('مبيعات بطاقة')->formatStateUsing(fn ($state) => $state->format()),

                Tables\Columns\TextColumn::make('expected_minor')
                    ->label('المتوقع بالدرج')->formatStateUsing(fn ($state) => $state?->format() ?? '—'),

                Tables\Columns\TextColumn::make('closing_counted_minor')
                    ->label('المعدود فعليًا')->formatStateUsing(fn ($state) => $state?->format() ?? '—'),

                // العمود الحاسم: العجز أو الزيادة
                Tables\Columns\TextColumn::make('variance_minor')
                    ->label('الفرق')
                    ->formatStateUsing(fn ($state) => $state?->format() ?? '—')
                    ->weight('bold')
                    ->color(fn (RegisterSession $r) => match (true) {
                        ! $r->variance_minor          => 'gray',
                        $r->variance_minor->isNegative() => 'danger',
                        $r->variance_minor->isZero()    => 'success',
                        default                       => 'warning',
                    }),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')->options(['open' => 'مفتوح', 'closed' => 'مغلق']),

                Tables\Filters\Filter::make('has_shortage')
                    ->label('به عجز')
                    ->query(fn (Builder $q) => $q->where('variance_minor', '<', 0)),
            ])
            ->defaultSort('opened_at', 'desc');
    }

    public static function getPages(): array
    {
        return ['index' => Pages\ListRegisterSessions::route('/')];
    }
}
