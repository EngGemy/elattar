<?php

declare(strict_types=1);

namespace App\Filament\Pages;

use App\Domain\Crm\Models\Customer;
use App\Domain\Pos\Actions\CheckoutPosAction;
use App\Domain\Pos\Actions\CloseRegisterSessionAction;
use App\Domain\Pos\Actions\OpenRegisterSessionAction;
use App\Domain\Pos\Models\Register;
use App\Domain\Pos\Models\RegisterSession;
use App\Domain\Pos\Services\PosCatalogBuilder;
use App\Domain\Sales\Models\Order;
use App\Domain\Shared\Enums\SalesChannel;
use App\Domain\Shared\ValueObjects\Money;
use App\Filament\Resources\OrderResource;
use App\Support\StorefrontCheckout;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Illuminate\Support\Str;

/**
 * شاشة الكاشير الاحترافية.
 * الكتالوج والبحث والسلة في المتصفح — السيرفر عند الدفع والتحديثات الدورية فقط.
 */
class PosTerminal extends Page
{
    protected static ?string $navigationIcon  = 'heroicon-o-calculator';
    protected static ?string $navigationLabel = 'نقطة البيع';
    protected static ?string $title           = 'نقطة البيع';
    protected static ?int    $navigationSort  = 0;

    protected static string $view = 'filament.pages.pos-terminal';

    /** شيفت الكاشير — لا نسمّيه session لتجنّب تعارض Livewire/Laravel */
    public ?RegisterSession $registerSession = null;

    /** @var array<int, array<string, mixed>> */
    public array $catalog = [];

    /** @var array<int, array<string, mixed>> */
    public array $categories = [];

    /** @var array<string, mixed> */
    public array $sessionMeta = [];

    public int $pendingOnline = 0;

    /** @var array<int, array<string, mixed>> */
    public array $pendingOrders = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['admin', 'cashier']) ?? false;
    }

    public function mount(): void
    {
        $register = Register::where('is_active', true)->first();

        $this->registerSession = $register?->openSession()?->loadMissing('register');
        $this->loadCatalog();
        $this->sessionMeta     = $this->buildSessionMeta();
        $this->pendingOnline   = $this->countPendingOnline();
        $this->pendingOrders   = $this->buildPendingOrders();
    }

    /** @return array<string, mixed> */
    protected function getViewData(): array
    {
        return [
            'registerSession' => $this->registerSession,
            'catalog'         => $this->catalog,
            'categories'      => $this->categories,
            'sessionMeta'     => $this->sessionMeta,
            'pendingOnline'   => $this->pendingOnline,
            'pendingOrders'   => $this->pendingOrders,
        ];
    }

    private function loadCatalog(): void
    {
        $rawWarehouseId = $this->registerSession?->register?->warehouse_id;
        $warehouseId = $rawWarehouseId !== null && $rawWarehouseId !== ''
            ? (int) $rawWarehouseId
            : null;

        $builder = app(PosCatalogBuilder::class);
        $this->catalog    = $builder->build($warehouseId);
        $this->categories = $builder->categoriesFor($this->catalog);
    }

    /** @return array<string, mixed> */
    private function buildSessionMeta(): array
    {
        if (! $this->registerSession) {
            return [];
        }

        $s = $this->registerSession->fresh();
        $captured = $s->tenders()->captured();

        return [
            'id'           => $s->id,
            'register'     => $s->register->name ?? 'كاشير',
            'cashier'      => $s->user->name ?? auth()->user()?->name,
            'opened_at'    => $s->opened_at?->format('H:i'),
            'orders_count' => (int) $captured->distinct('order_id')->count('order_id'),
            'cash_sales'   => (int) $captured->where('method', 'cash')->sum('amount_minor'),
            'card_sales'   => (int) $captured->where('method', 'card')->sum('amount_minor'),
            'opening'      => (int) $s->getRawOriginal('opening_float_minor'),
        ];
    }

    private function countPendingOnline(): int
    {
        return count($this->buildPendingOrders());
    }

    /** @return array<int, array<string, mixed>> */
    private function buildPendingOrders(): array
    {
        try {
            return Order::query()
                ->where('status', 'pending')
                ->where('channel', SalesChannel::Online)
                ->with(['customer', 'lines'])
                ->latest('placed_at')
                ->limit(25)
                ->get()
                ->map(fn (Order $o) => [
                    'id'          => $o->id,
                    'number'      => $o->number,
                    'customer'    => $o->customer?->name ?? ($o->shipping_address['recipient_name'] ?? 'عميل'),
                    'phone'       => $o->customer?->phone ?? ($o->shipping_address['phone'] ?? ''),
                    'city'        => $o->shipping_address['city'] ?? '',
                    'total'       => (int) $o->getRawOriginal('total_minor'),
                    'items_count' => $o->lines->count(),
                    'placed_at'   => $o->placed_at?->format('d/m H:i') ?? '',
                    'placed_ago'  => $o->placed_at?->diffForHumans() ?? '',
                    'payment'     => StorefrontCheckout::paymentLabel($o->shipping_address['payment_method'] ?? 'cod'),
                    'url'         => OrderResource::getUrl('view', ['record' => $o]),
                ])
                ->all();
        } catch (\Throwable) {
            return [];
        }
    }

    /**
     * بحث عملاء سريع.
     * عند ترك الحقل فارغًا نعرض أحدث العملاء تعاملًا للتصفّح المباشر.
     */
    public function searchCustomers(string $query = ''): array
    {
        $query = trim($query);

        $builder = Customer::query()->where('is_active', true);

        if (mb_strlen($query) >= 1) {
            $builder
                ->where(function ($q) use ($query) {
                    $q->where('name', 'like', "%{$query}%")
                        ->orWhere('phone', 'like', "%{$query}%");
                })
                ->orderBy('name');
        } else {
            $builder->orderByDesc('updated_at');
        }

        return $builder
            ->limit(12)
            ->get(['id', 'name', 'phone'])
            ->toArray();
    }

    /** تحديث دوري — طلبات معلّقة + أرصدة */
    public function pollUpdates(): array
    {
        $prev = $this->pendingOnline;
        $this->pendingOrders = $this->buildPendingOrders();
        $this->pendingOnline = count($this->pendingOrders);
        $this->sessionMeta   = $this->buildSessionMeta();

        return [
            'pending_online' => $this->pendingOnline,
            'pending_orders' => $this->pendingOrders,
            'new_online'     => $this->pendingOnline > $prev,
            'session'        => $this->sessionMeta,
        ];
    }

    public function refreshCatalog(): void
    {
        $this->loadCatalog();
    }

    public function openSession(int $registerId, float $openingFloat): void
    {
        try {
            $this->registerSession = app(OpenRegisterSessionAction::class)
                ->execute(Register::findOrFail($registerId), Money::ofMajor($openingFloat))
                ->loadMissing('register');

            $this->loadCatalog();
            $this->sessionMeta   = $this->buildSessionMeta();
            $this->pendingOnline = $this->countPendingOnline();
            $this->pendingOrders = $this->buildPendingOrders();

            Notification::make()->title('تم فتح الشيفت')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('تعذّر فتح الشيفت')->body($e->getMessage())->danger()->send();
        }
    }

    public function checkout(array $items, array $payments, ?int $customerId = null): void
    {
        if (! $this->registerSession) {
            Notification::make()->title('لا يوجد شيفت مفتوح')->danger()->send();

            return;
        }

        try {
            $order = app(CheckoutPosAction::class)->execute(
                session:        $this->registerSession,
                items:          $items,
                payments:       $payments,
                customerId:     $customerId,
                idempotencyKey: (string) Str::uuid(),
            );

            $order->load('tenders');
            $tender = $order->tenders->first();

            $this->dispatch(
                'order-completed',
                orderId:  $order->id,
                number:   $order->number,
                total:    (int) $order->getRawOriginal('total_minor'),
                tendered: (int) ($tender?->getRawOriginal('tendered_minor') ?? $order->getRawOriginal('total_minor')),
                change:   (int) ($tender?->getRawOriginal('change_minor') ?? 0),
            );

            Notification::make()
                ->title("تم البيع — فاتورة {$order->number}")
                ->body(
                    'الإجمالي: ' . $order->total_minor->format()
                    . (($tender?->change_minor?->isPositive())
                        ? ' | الباقي: ' . $tender->change_minor->format()
                        : '')
                )
                ->success()->send();

            $this->refreshCatalog();
            $this->sessionMeta = $this->buildSessionMeta();
            $this->pendingOrders = $this->buildPendingOrders();
            $this->pendingOnline = count($this->pendingOrders);
        } catch (\Throwable $e) {
            Notification::make()->title('فشلت عملية البيع')->body($e->getMessage())->danger()->send();
        }
    }

    public function closeSession(float $countedCash, ?string $note = null): void
    {
        try {
            $closed = app(CloseRegisterSessionAction::class)
                ->execute($this->registerSession, Money::ofMajor($countedCash), $note);

            $variance = $closed->variance_minor;

            Notification::make()
                ->title('تم إقفال الشيفت')
                ->body($variance->isZero()
                    ? 'الصندوق مطابق تمامًا.'
                    : ($variance->isNegative() ? 'عجز: ' : 'زيادة: ') . $variance->format())
                ->color($variance->isNegative() ? 'danger' : 'success')
                ->send();

            $this->registerSession = null;
        } catch (\Throwable $e) {
            Notification::make()->title('تعذّر الإقفال')->body($e->getMessage())->danger()->send();
        }
    }
}
