<?php

declare(strict_types=1);

namespace App\Filament\Resources;

use App\Filament\Resources\OrderResource\Pages;
use App\Domain\Sales\Actions\CancelOrderAction;
use App\Domain\Sales\Actions\FulfillOrderAction;
use App\Domain\Sales\Actions\RecordPaymentAction;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\States\Confirmed;
use App\Domain\Sales\States\Delivered;
use App\Domain\Sales\States\Processing;
use App\Domain\Shared\Enums\PaymentMethod;
use App\Domain\Shared\Enums\PaymentStatus;
use App\Domain\Shared\Enums\SalesChannel;
use App\Filament\Clusters\SalesCluster;
use App\Support\StorefrontCheckout;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model            = Order::class;
    protected static ?string $cluster          = SalesCluster::class;
    protected static ?string $navigationIcon   = 'heroicon-o-shopping-bag';
    protected static ?string $navigationLabel  = 'الطلبات';
    protected static ?string $modelLabel       = 'طلب';
    protected static ?string $pluralModelLabel = 'الطلبات';
    protected static ?int    $navigationSort   = 1;

    /** عدّاد الطلبات المعلّقة في القائمة الجانبية */
    public static function getNavigationBadge(): ?string
    {
        $count = static::getModel()::where('status', 'pending')->count();

        return $count > 0 ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function form(Form $form): Form
    {
        // الطلبات تُنشأ عبر الـ Actions لا عبر النموذج — التعديل يقتصر على الحقول الإدارية
        return $form->schema([
            Forms\Components\Section::make('بيانات الشحن')->schema([
                Forms\Components\TextInput::make('shipping_carrier')->label('شركة الشحن'),
                Forms\Components\TextInput::make('tracking_number')->label('رقم التتبّع'),
                Forms\Components\Textarea::make('internal_notes')->label('ملاحظات داخلية')->rows(3),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('number')
                    ->label('رقم الطلب')->searchable()->sortable()
                    ->copyable()->fontFamily('mono')->weight('bold'),

                Tables\Columns\TextColumn::make('customer.name')
                    ->label('العميل')->searchable()
                    ->default('عميل عابر')->description(fn (Order $r) => $r->customer?->phone),

                Tables\Columns\TextColumn::make('channel')
                    ->label('القناة')->badge()->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label('الحالة')
                    ->badge()
                    ->formatStateUsing(fn ($state) => $state->label())
                    ->color(fn ($state) => $state->color())
                    ->icon(fn ($state) => $state->icon()),

                Tables\Columns\TextColumn::make('payment_status')
                    ->label('السداد')->badge(),

                Tables\Columns\TextColumn::make('total_minor')
                    ->label('الإجمالي')
                    ->formatStateUsing(fn ($state) => $state->format())
                    ->sortable()->weight('bold')
                    ->summarize(Tables\Columns\Summarizers\Sum::make()
                        ->formatStateUsing(fn ($state) => number_format($state / 100, 2) . ' ج.م')),

                // ⚠ الربح مخفي عن الكاشير
                Tables\Columns\TextColumn::make('profit')
                    ->label('الربح')
                    ->visible(fn () => ! auth()->user()?->isCashier())
                    ->state(fn (Order $r) => $r->grossProfit()->format())
                    ->description(fn (Order $r) => $r->grossMarginPercent() . '%')
                    ->color(fn (Order $r) => $r->grossMarginPercent() < 15 ? 'danger' : 'success'),

                Tables\Columns\TextColumn::make('payment_method')
                    ->label('طريقة الدفع')
                    ->state(fn (Order $r) => $r->paymentMethodLabel())
                    ->toggleable(),

                Tables\Columns\TextColumn::make('placed_at')
                    ->label('التاريخ')->dateTime('Y-m-d H:i')->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('الحالة')
                    ->options([
                        'pending'    => 'قيد الانتظار',
                        'confirmed'  => 'مؤكَّد',
                        'processing' => 'قيد التجهيز',
                        'shipped'    => 'تم الشحن',
                        'delivered'  => 'تم التسليم',
                        'cancelled'  => 'ملغي',
                        'returned'   => 'مرتجع',
                    ]),

                Tables\Filters\SelectFilter::make('channel')
                    ->label('القناة')->options(SalesChannel::class),

                Tables\Filters\SelectFilter::make('payment_status')
                    ->label('حالة السداد')->options(PaymentStatus::class),

                Tables\Filters\Filter::make('placed_at')
                    ->form([
                        Forms\Components\DatePicker::make('from')->label('من تاريخ'),
                        Forms\Components\DatePicker::make('to')->label('إلى تاريخ'),
                    ])
                    ->query(fn (Builder $q, array $data) => $q
                        ->when($data['from'], fn ($q, $v) => $q->whereDate('placed_at', '>=', $v))
                        ->when($data['to'],   fn ($q, $v) => $q->whereDate('placed_at', '<=', $v))),
            ])
            ->actions([
                Tables\Actions\ViewAction::make()->label('عرض'),

                // ── الانتقالات المسموحة فقط تظهر. آلة الحالات هي المصدر الوحيد للحقيقة.
                Tables\Actions\Action::make('confirm')
                    ->label('تأكيد')->icon('heroicon-o-check-circle')->color('info')
                    ->visible(fn (Order $r) => $r->status->canTransitionTo(Confirmed::class))
                    ->requiresConfirmation()
                    ->action(fn (Order $r) => $r->status->transitionTo(Confirmed::class)),

                Tables\Actions\Action::make('process')
                    ->label('بدء التجهيز')->icon('heroicon-o-cog-6-tooth')->color('warning')
                    ->visible(fn (Order $r) => $r->status->canTransitionTo(Processing::class))
                    ->action(fn (Order $r) => $r->status->transitionTo(Processing::class)),

                Tables\Actions\Action::make('fulfill')
                    ->label('صرف وشحن')->icon('heroicon-o-truck')->color('primary')
                    ->visible(fn (Order $r) => $r->status::$name === 'processing')
                    ->requiresConfirmation()
                    ->modalDescription('سيتم خصم الكميات من المخزون. لا يمكن التراجع إلا بحركة عكسية.')
                    ->action(function (Order $r) {
                        try {
                            app(FulfillOrderAction::class)->execute($r);
                            Notification::make()->title('تم صرف الطلب وخصم المخزون')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('فشل الصرف')->body($e->getMessage())->danger()->send();
                        }
                    }),

                Tables\Actions\Action::make('deliver')
                    ->label('تأكيد التسليم')->icon('heroicon-o-check-badge')->color('success')
                    ->visible(fn (Order $r) => $r->status->canTransitionTo(Delivered::class))
                    ->action(function (Order $r) {
                        $r->status->transitionTo(Delivered::class);
                        $r->update(['delivered_at' => now()]);

                        if (($r->shipping_address['payment_method'] ?? 'cod') === 'cod' && ! $r->isPaid()) {
                            app(RecordPaymentAction::class)->execute(
                                $r,
                                PaymentMethod::Cod,
                                $r->balanceDue()
                            );
                        }
                    }),

                Tables\Actions\Action::make('cancel')
                    ->label('إلغاء')->icon('heroicon-o-x-circle')->color('danger')
                    ->visible(fn (Order $r) => ! $r->status->isFinal())
                    ->form([Forms\Components\Textarea::make('reason')->label('سبب الإلغاء')->required()])
                    ->action(function (Order $r, array $data) {
                        try {
                            app(CancelOrderAction::class)->execute($r, $data['reason']);
                            Notification::make()->title('تم إلغاء الطلب')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('فشل الإلغاء')->body($e->getMessage())->danger()->send();
                        }
                    }),

                Tables\Actions\Action::make('confirm_payment')
                    ->label('تأكيد الدفع')->icon('heroicon-o-banknotes')->color('success')
                    ->visible(fn (Order $r) => ! $r->isPaid())
                    ->requiresConfirmation()
                    ->modalHeading('تأكيد استلام المبلغ')
                    ->modalDescription(fn (Order $r) => 'تسجيل دفع ' . $r->balanceDue()->format()
                        . ' — ' . $r->paymentMethodLabel())
                    ->action(function (Order $r) {
                        $method = match ($r->shipping_address['payment_method'] ?? 'cod') {
                            'instapay'      => PaymentMethod::Transfer,
                            'vodafone_cash' => PaymentMethod::Wallet,
                            default         => PaymentMethod::Cod,
                        };

                        try {
                            app(RecordPaymentAction::class)->execute($r, $method, $r->balanceDue());
                            Notification::make()->title('تم تسجيل الدفع')->success()->send();
                        } catch (\Throwable $e) {
                            Notification::make()->title('فشل تسجيل الدفع')->body($e->getMessage())->danger()->send();
                        }
                    }),

                Tables\Actions\Action::make('invoice')
                    ->label('الفاتورة')->icon('heroicon-o-document-arrow-down')
                    ->url(fn (Order $r) => route('orders.invoice', $r))
                    ->openUrlInNewTab(),
            ])
            ->poll('8s')
            ->defaultSort('placed_at', 'desc');
    }

    public static function infolist(Infolist $infolist): Infolist
    {
        return $infolist->schema([
            Infolists\Components\Section::make('ملخص الطلب')->schema([
                Infolists\Components\TextEntry::make('number')->label('رقم الطلب')->weight('bold'),
                Infolists\Components\TextEntry::make('status')
                    ->label('الحالة')->badge()
                    ->formatStateUsing(fn ($state) => $state->label())->color(fn ($state) => $state->color()),
                Infolists\Components\TextEntry::make('payment_status')
                    ->label('حالة السداد')->badge(),
                Infolists\Components\TextEntry::make('payment_method')
                    ->label('طريقة الدفع')
                    ->state(fn (Order $r) => $r->paymentMethodLabel()),
                Infolists\Components\TextEntry::make('tendered_total')
                    ->label('المدفوع من العميل')
                    ->state(fn (Order $r) => $r->totalTendered()->format())
                    ->visible(fn (Order $r) => $r->channel === SalesChannel::Pos),
                Infolists\Components\TextEntry::make('change_total')
                    ->label('الباقي للعميل')
                    ->state(fn (Order $r) => $r->totalChange()->format())
                    ->visible(fn (Order $r) => $r->channel === SalesChannel::Pos && $r->totalChange()->isPositive())
                    ->color('success'),
                Infolists\Components\TextEntry::make('transfer_number')
                    ->label('رقم التحويل')
                    ->state(fn (Order $r) => StorefrontCheckout::paymentNumber(
                        (string) ($r->shipping_address['payment_method'] ?? '')
                    ) ?? '—')
                    ->visible(fn (Order $r) => in_array($r->shipping_address['payment_method'] ?? '', ['instapay', 'vodafone_cash'], true)),
                Infolists\Components\TextEntry::make('customer.name')->label('العميل')->default('عميل عابر'),
                Infolists\Components\TextEntry::make('placed_at')->label('تاريخ الطلب')->dateTime('Y-m-d H:i'),
            ])->columns(4),

            Infolists\Components\Section::make('الأصناف')->schema([
                Infolists\Components\RepeatableEntry::make('lines')
                    ->label('')
                    ->schema([
                        Infolists\Components\TextEntry::make('name_snapshot')->label('الصنف'),
                        Infolists\Components\TextEntry::make('qty')
                            ->label('الكمية')
                            ->formatStateUsing(fn ($state, $record) => $record->quantity()->format()),
                        Infolists\Components\TextEntry::make('unit_price_minor')
                            ->label('سعر الوحدة')->formatStateUsing(fn ($state) => $state->format()),
                        Infolists\Components\TextEntry::make('line_total_minor')
                            ->label('الإجمالي')->formatStateUsing(fn ($state) => $state->format())->weight('bold'),
                    ])->columns(4),
            ]),

            Infolists\Components\Section::make('الحسابات')->schema([
                Infolists\Components\TextEntry::make('subtotal_minor')->label('الإجمالي قبل الخصم')->formatStateUsing(fn ($state) => $state->format()),
                Infolists\Components\TextEntry::make('discount_minor')->label('الخصم')->formatStateUsing(fn ($state) => '- ' . $state->format())->color('danger'),
                Infolists\Components\TextEntry::make('tax_minor')->label('ضريبة القيمة المضافة')->formatStateUsing(fn ($state) => $state->format()),
                Infolists\Components\TextEntry::make('shipping_minor')->label('الشحن')->formatStateUsing(fn ($state) => $state->format()),
                Infolists\Components\TextEntry::make('paid_minor')->label('المدفوع')->formatStateUsing(fn ($state) => $state->format())->color('success'),
                Infolists\Components\TextEntry::make('balance_due')
                    ->label('المتبقي')
                    ->state(fn (Order $r) => $r->balanceDue()->format())
                    ->color(fn (Order $r) => $r->balanceDue()->isPositive() ? 'danger' : 'success'),
                Infolists\Components\TextEntry::make('total_minor')->label('الإجمالي النهائي')->formatStateUsing(fn ($state) => $state->format())->weight('bold')->size('lg'),

                // مؤشرات مالية — للإدارة فقط
                Infolists\Components\TextEntry::make('cogs')
                    ->label('تكلفة البضاعة المباعة')
                    ->visible(fn () => ! auth()->user()?->isCashier())
                    ->state(fn (Order $r) => $r->cogs_minor->format()),

                Infolists\Components\TextEntry::make('gross_profit')
                    ->label('مجمل الربح')
                    ->visible(fn () => ! auth()->user()?->isCashier())
                    ->state(fn (Order $r) => $r->grossProfit()->format() . '  (' . $r->grossMarginPercent() . '%)')
                    ->weight('bold')
                    ->color(fn (Order $r) => $r->grossMarginPercent() < 15 ? 'danger' : 'success'),
            ])->columns(3),

            Infolists\Components\Section::make('نقاط الدفع')
                ->schema([
                    Infolists\Components\RepeatableEntry::make('tenders')
                        ->label('')
                        ->schema([
                            Infolists\Components\TextEntry::make('method')
                                ->label('الطريقة')
                                ->formatStateUsing(fn ($state) => $state->getLabel()),
                            Infolists\Components\TextEntry::make('amount_minor')
                                ->label('مبلغ الفاتورة')
                                ->formatStateUsing(fn ($state) => $state->format()),
                            Infolists\Components\TextEntry::make('tendered_minor')
                                ->label('المدفوع')
                                ->formatStateUsing(fn ($state) => $state?->format() ?? '—'),
                            Infolists\Components\TextEntry::make('change_minor')
                                ->label('الباقي')
                                ->formatStateUsing(fn ($state) => $state?->format() ?? '—'),
                        ])->columns(4),
                ])
                ->visible(fn (Order $r) => $r->tenders()->exists()),

            Infolists\Components\Section::make('سجل الحالة')->schema([
                Infolists\Components\RepeatableEntry::make('statusHistory')
                    ->label('')
                    ->schema([
                        Infolists\Components\TextEntry::make('to_status')->label('الحالة'),
                        Infolists\Components\TextEntry::make('note')->label('ملاحظة'),
                        Infolists\Components\TextEntry::make('user.name')->label('بواسطة')->default('النظام'),
                        Infolists\Components\TextEntry::make('created_at')->label('التوقيت')->dateTime('Y-m-d H:i'),
                    ])->columns(4),
            ])->collapsible(),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->with(['customer', 'lines', 'tenders']);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'view'  => Pages\ViewOrder::route('/{record}'),
            'edit'  => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
