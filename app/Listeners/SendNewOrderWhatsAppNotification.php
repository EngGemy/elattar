<?php

declare(strict_types=1);

namespace App\Listeners;

use App\Domain\Sales\Events\OrderPlaced;
use App\Domain\Shared\Enums\SalesChannel;
use App\Jobs\SendNewOrderWhatsAppNotificationJob;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * يطلق إشعار واتساب لصاحب المتجر بعد إنشاء طلب أونلاين — بشكل غير متزامن.
 */
class SendNewOrderWhatsAppNotification implements ShouldQueue
{
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;

        if ($order->channel !== SalesChannel::Online) {
            return;
        }

        if (! config('services.whatsapp.notify_enabled', false)) {
            return;
        }

        SendNewOrderWhatsAppNotificationJob::dispatch($order->id);
    }
}
