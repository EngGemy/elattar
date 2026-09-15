<?php

declare(strict_types=1);

namespace App\Jobs;

use App\Domain\Notifications\Contracts\WhatsAppServiceInterface;
use App\Domain\Sales\Models\Order;
use App\Support\PhoneNumber;
use App\Support\ShopSettings;
use App\Support\StorefrontWhatsApp;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

class SendNewOrderWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public int $tries = 3;

    /** @var list<int> */
    public array $backoff = [30, 60, 120];

    public function __construct(public readonly int $orderId) {}

    public function handle(WhatsAppServiceInterface $whatsApp): void
    {
        if (! config('services.whatsapp.notify_enabled', false)) {
            Log::debug('whatsapp.notify.disabled', ['order_id' => $this->orderId]);

            return;
        }

        if (! $whatsApp->isConfigured()) {
            Log::warning('whatsapp.notify.not_configured', ['order_id' => $this->orderId]);

            return;
        }

        $order = Order::query()
            ->with(['lines', 'customer'])
            ->find($this->orderId);

        if (! $order) {
            Log::warning('whatsapp.notify.order_missing', ['order_id' => $this->orderId]);

            return;
        }

        $to = PhoneNumber::toE164(ShopSettings::whatsapp());

        if ($to === null) {
            Log::error('whatsapp.notify.invalid_shop_phone', [
                'order_id' => $this->orderId,
                'raw'      => ShopSettings::whatsapp(),
            ]);

            return;
        }

        $message = StorefrontWhatsApp::notifyShopMessage($order);

        $whatsApp->sendText($to, $message);
    }

    public function failed(?Throwable $exception): void
    {
        Log::error('whatsapp.notify.job_failed', [
            'order_id' => $this->orderId,
            'error'    => $exception?->getMessage(),
        ]);
    }
}
