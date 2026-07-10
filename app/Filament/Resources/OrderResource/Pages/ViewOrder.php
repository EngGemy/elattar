<?php

declare(strict_types=1);

namespace App\Filament\Resources\OrderResource\Pages;

use App\Domain\Sales\Actions\CancelOrderAction;
use App\Domain\Sales\Actions\FulfillOrderAction;
use App\Domain\Sales\Actions\RecordPaymentAction;
use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Models\OrderStatusHistory;
use App\Domain\Sales\States\Confirmed;
use App\Domain\Sales\States\Delivered;
use App\Domain\Sales\States\Processing;
use App\Domain\Shared\Enums\PaymentMethod;
use App\Domain\Shared\Enums\SalesChannel;
use App\Filament\Resources\OrderResource;
use App\Support\StorefrontCheckout;
use Filament\Actions;
use Filament\Forms;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Throwable;

class ViewOrder extends ViewRecord
{
    protected static string $resource = OrderResource::class;

    protected static string $view = 'filament.resources.orders.view-order';

    public ?string $internalNotes = null;

    public ?string $shippingCarrier = null;

    public ?string $trackingNumber = null;

    public string $cancelReason = '';

    public bool $showCancelForm = false;

    public function mount(int | string $record): void
    {
        parent::mount($record);

        /** @var Order $order */
        $order = $this->getRecord();

        $this->internalNotes   = $order->internal_notes ?? '';
        $this->shippingCarrier = $order->shipping_carrier ?? '';
        $this->trackingNumber  = $order->tracking_number ?? '';
    }

    public function saveInternalNotes(): void
    {
        abort_unless(static::getResource()::canView($this->getRecord()), 403);

        $this->getRecord()->update([
            'internal_notes' => trim($this->internalNotes ?? '') ?: null,
        ]);

        Notification::make()->title('تم حفظ الملاحظات')->success()->send();
    }

    public function saveShipping(): void
    {
        abort_unless(static::getResource()::canView($this->getRecord()), 403);

        $this->getRecord()->update([
            'shipping_carrier' => trim($this->shippingCarrier ?? '') ?: null,
            'tracking_number'  => trim($this->trackingNumber ?? '') ?: null,
        ]);

        $this->record->refresh();

        Notification::make()->title('تم حفظ بيانات الشحن')->success()->send();
    }

    public function confirmOrder(): void
    {
        $this->runStatusAction(function (Order $order) {
            if (! $order->status->canTransitionTo(Confirmed::class)) {
                return;
            }

            $this->logTransition($order, Confirmed::class, 'تم تأكيد الطلب');
            Notification::make()->title('تم تأكيد الطلب')->success()->send();
        });
    }

    public function startProcessing(): void
    {
        $this->runStatusAction(function (Order $order) {
            if (! $order->status->canTransitionTo(Processing::class)) {
                return;
            }

            $this->logTransition($order, Processing::class, 'بدء تجهيز الطلب');
            Notification::make()->title('بدء التجهيز')->success()->send();
        });
    }

    public function fulfillOrder(): void
    {
        $this->runStatusAction(function (Order $order) {
            if ($order->status::$name !== 'processing') {
                return;
            }

            app(FulfillOrderAction::class)->execute($order);
            $this->record->refresh();
            Notification::make()->title('تم صرف الطلب وخصم المخزون')->success()->send();
        });
    }

    public function deliverOrder(): void
    {
        $this->runStatusAction(function (Order $order) {
            if (! $order->status->canTransitionTo(Delivered::class)) {
                return;
            }

            $this->logTransition($order, Delivered::class, 'تم تسليم الطلب للعميل');
            $order->update(['delivered_at' => now()]);

            $fresh = $order->fresh();

            if (($fresh->shipping_address['payment_method'] ?? 'cod') === 'cod' && ! $fresh->isPaid()) {
                app(RecordPaymentAction::class)->execute(
                    $fresh,
                    PaymentMethod::Cod,
                    $fresh->balanceDue()
                );
            }

            $this->record->refresh();
            Notification::make()->title('تم تأكيد التسليم')->success()->send();
        });
    }

    public function confirmPayment(): void
    {
        $this->runStatusAction(function (Order $order) {
            if ($order->isPaid()) {
                Notification::make()->title('الطلب مسدَّد بالفعل')->warning()->send();

                return;
            }

            $method = $this->resolvePaymentMethod($order);

            app(RecordPaymentAction::class)->execute($order, $method, $order->balanceDue());

            $this->record->refresh();
            Notification::make()->title('تم تسجيل التحصيل')->success()->send();
        });
    }

    public function cancelOrder(): void
    {
        $reason = trim($this->cancelReason);

        if ($reason === '') {
            Notification::make()->title('سبب الإلغاء مطلوب')->danger()->send();

            return;
        }

        $this->runStatusAction(function (Order $order) use ($reason) {
            if ($order->status->isFinal()) {
                return;
            }

            app(CancelOrderAction::class)->execute($order, $reason);

            $this->cancelReason   = '';
            $this->showCancelForm = false;
            $this->record->refresh();

            Notification::make()->title('تم إلغاء الطلب')->success()->send();
        });
    }

    public function getTitle(): string
    {
        return '';
    }

    public function getHeading(): string
    {
        return '';
    }

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('invoice')
                ->label('طباعة الفاتورة')
                ->icon('heroicon-o-printer')
                ->color('gray')
                ->url(fn (Order $record) => route('orders.invoice', $record))
                ->openUrlInNewTab(),

            Actions\Action::make('confirm')
                ->label('تأكيد الطلب')
                ->icon('heroicon-o-check-circle')
                ->color('info')
                ->visible(fn (Order $record) => $record->status->canTransitionTo(Confirmed::class))
                ->requiresConfirmation()
                ->action(fn () => $this->confirmOrder()),

            Actions\Action::make('process')
                ->label('بدء التجهيز')
                ->icon('heroicon-o-cog-6-tooth')
                ->color('warning')
                ->visible(fn (Order $record) => $record->status->canTransitionTo(Processing::class))
                ->action(fn () => $this->startProcessing()),

            Actions\Action::make('fulfill')
                ->label('صرف وشحن')
                ->icon('heroicon-o-truck')
                ->color('primary')
                ->visible(fn (Order $record) => $record->status::$name === 'processing')
                ->requiresConfirmation()
                ->modalDescription('سيتم خصم الكميات من المخزون.')
                ->action(fn () => $this->fulfillOrder()),

            Actions\Action::make('deliver')
                ->label('تأكيد التسليم')
                ->icon('heroicon-o-check-badge')
                ->color('success')
                ->visible(fn (Order $record) => $record->status->canTransitionTo(Delivered::class))
                ->requiresConfirmation()
                ->action(fn () => $this->deliverOrder()),

            Actions\Action::make('confirm_payment')
                ->label('تأكيد الدفع')
                ->icon('heroicon-o-banknotes')
                ->color('success')
                ->visible(fn (Order $record) => ! $record->isPaid())
                ->requiresConfirmation()
                ->modalHeading('تأكيد استلام المبلغ')
                ->modalDescription(fn (Order $record) => 'تسجيل دفع ' . $record->balanceDue()->format()
                    . ' — ' . $record->paymentMethodLabel())
                ->action(fn () => $this->confirmPayment()),

            Actions\Action::make('cancel')
                ->label('إلغاء')
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn (Order $record) => ! $record->status->isFinal())
                ->form([Forms\Components\Textarea::make('reason')->label('سبب الإلغاء')->required()])
                ->action(function (array $data) {
                    $this->cancelReason = $data['reason'];
                    $this->cancelOrder();
                }),
        ];
    }

    /** @return array<string, mixed> */
    public function getViewData(): array
    {
        /** @var Order $order */
        $order = $this->record->load(['customer', 'lines', 'tenders', 'statusHistory.user', 'warehouse']);

        return array_merge(parent::getViewData(), ['order' => $order] + $this->buildOrderViewMeta($order));
    }

    /** @return array<string, mixed> */
    private function buildOrderViewMeta(Order $order): array
    {
        $statusName = $order->status::$name ?? 'pending';

        $steps = [
            ['key' => 'pending',    'label' => 'انتظار',   'icon' => '⏳'],
            ['key' => 'confirmed',  'label' => 'تأكيد',    'icon' => '✅'],
            ['key' => 'processing', 'label' => 'تجهيز',    'icon' => '⚙️'],
            ['key' => 'shipped',    'label' => 'شحن',      'icon' => '🚚'],
            ['key' => 'delivered',  'label' => 'تسليم',    'icon' => '📦'],
        ];

        $stepIndex = collect($steps)->search(fn ($s) => $s['key'] === $statusName);

        $addr = $order->shipping_address ?? [];
        $totalMinor = (int) $order->getRawOriginal('total_minor');
        $paidMinor  = (int) $order->getRawOriginal('paid_minor');
        $paidPct    = $totalMinor > 0 ? min(100, round($paidMinor / $totalMinor * 100)) : 0;

        $paymentStatus = $order->payment_status->value;
        $payMethod     = (string) ($addr['payment_method'] ?? 'cod');

        return [
            'steps'            => $steps,
            'stepIndex'        => $stepIndex === false ? 0 : $stepIndex,
            'isFinal'          => $order->status->isFinal(),
            'isCancelled'      => $statusName === 'cancelled',
            'isReturned'       => $statusName === 'returned',
            'channelLabel'     => $order->channel === SalesChannel::Pos ? 'نقطة البيع' : 'المتجر الإلكتروني',
            'addr'             => $addr,
            'paidPct'          => $paidPct,
            'isCashier'        => auth()->user()?->isCashier() ?? false,
            'nextAction'       => $this->resolveNextActionHint($order, $statusName),
            'invoiceUrl'       => route('orders.invoice', $order),
            'invoicePrintUrl'  => route('orders.invoice', $order) . '?autoprint=1',
            'canRecordPayment' => ! $order->isPaid(),
            'paymentButtonLabel' => $this->paymentButtonLabel($payMethod),
            'ops'              => [
                'canConfirm' => $order->status->canTransitionTo(Confirmed::class),
                'canProcess' => $order->status->canTransitionTo(Processing::class),
                'canFulfill' => $statusName === 'processing',
                'canDeliver' => $order->status->canTransitionTo(Delivered::class),
                'canCancel'  => ! $order->status->isFinal(),
            ],
            'collection'       => $this->buildCollectionStatus($order, $paymentStatus),
        ];
    }

    /** @return array{icon: string, title: string, subtitle: string, tone: string} */
    private function buildCollectionStatus(Order $order, string $paymentStatus): array
    {
        return match ($paymentStatus) {
            'paid' => [
                'icon'     => '✅',
                'title'    => 'تم التحصيل بالكامل',
                'subtitle' => 'المبلغ محصَّل — ' . $order->paymentMethodLabel(),
                'tone'     => 'ok',
            ],
            'partial' => [
                'icon'     => '⚠️',
                'title'    => 'تحصيل جزئي',
                'subtitle' => 'تم تحصيل ' . $order->paid_minor->format() . ' — متبقي ' . $order->balanceDue()->format(),
                'tone'     => 'warn',
            ],
            'refunded', 'partially_refunded' => [
                'icon'     => '↩️',
                'title'    => $order->payment_status->getLabel(),
                'subtitle' => 'راجع سجل المرتجعات والدفعات',
                'tone'     => 'gray',
            ],
            default => [
                'icon'     => '❌',
                'title'    => 'لم يتم التحصيل',
                'subtitle' => 'المطلوب: ' . $order->total_minor->format() . ' — ' . $order->paymentMethodLabel(),
                'tone'     => 'bad',
            ],
        };
    }

    /** @return array{icon: string, label: string, hint: string}|null */
    private function resolveNextActionHint(Order $order, string $statusName): ?array
    {
        if ($order->status->isFinal()) {
            return null;
        }

        if (! $order->isPaid()) {
            return [
                'icon'  => '💳',
                'label' => $this->paymentButtonLabel((string) ($order->shipping_address['payment_method'] ?? 'cod')),
                'hint'  => 'سجّل استلام ' . $order->balanceDue()->format() . ' من قسم «التحصيل والدفع» أو الأزرار أدناه.',
            ];
        }

        return match ($statusName) {
            'pending'    => ['icon' => '✅', 'label' => 'الخطوة التالية: تأكيد الطلب', 'hint' => 'راجع الأصناف والمبلغ ثم أكّد الطلب.'],
            'confirmed'  => ['icon' => '⚙️', 'label' => 'الخطوة التالية: بدء التجهيز', 'hint' => 'ابدأ تجهيز الأصناف من المخزن.'],
            'processing' => ['icon' => '🚚', 'label' => 'الخطوة التالية: صرف وشحن', 'hint' => 'سيتم خصم الكميات من المخزون عند التنفيذ.'],
            'shipped'    => ['icon' => '📦', 'label' => 'الخطوة التالية: تأكيد التسليم', 'hint' => 'بعد وصول الطلب للعميل، أكّد التسليم.'],
            default      => null,
        };
    }

    private function paymentButtonLabel(string $payMethod): string
    {
        return match ($payMethod) {
            'instapay'      => 'تأكيد استلام التحويل (إنستاباي)',
            'vodafone_cash' => 'تأكيد استلام المحفظة',
            'cod'           => 'تأكيد استلام الكاش',
            default         => 'تأكيد استلام المبلغ',
        };
    }

    private function resolvePaymentMethod(Order $order): PaymentMethod
    {
        return match ($order->shipping_address['payment_method'] ?? 'cod') {
            'instapay'      => PaymentMethod::Transfer,
            'vodafone_cash' => PaymentMethod::Wallet,
            default         => PaymentMethod::Cod,
        };
    }

    /** @param class-string<\App\Domain\Sales\States\OrderState> $toState */
    private function logTransition(Order $order, string $toState, string $note): void
    {
        $from = $order->status::$name;
        $order->status->transitionTo($toState);

        OrderStatusHistory::create([
            'order_id'    => $order->id,
            'from_status' => $from,
            'to_status'   => $toState::$name,
            'note'        => $note,
            'user_id'     => auth()->id(),
        ]);

        $this->record->refresh();
    }

    /** @param callable(Order): void $callback */
    private function runStatusAction(callable $callback): void
    {
        abort_unless(static::getResource()::canView($this->getRecord()), 403);

        try {
            /** @var Order $order */
            $order = $this->getRecord()->fresh(['lines', 'customer']);
            $callback($order);
            $this->record->refresh();
        } catch (Throwable $e) {
            Notification::make()
                ->title('تعذّر تنفيذ العملية')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }
}
