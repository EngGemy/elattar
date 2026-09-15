<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Inventory\Events\LowStockDetected;
use App\Domain\Inventory\Events\StockLevelChanged;
use App\Domain\Notifications\Contracts\WhatsAppServiceInterface;
use App\Domain\Notifications\Services\GreenApiWhatsAppService;
use App\Domain\Notifications\Services\NullWhatsAppService;
use App\Domain\Pricing\Services\PromotionResolver;
use App\Domain\Sales\Events\OrderPlaced;
use App\Listeners\CheckReorderPoint;
use App\Listeners\SendLowStockAlert;
use App\Listeners\SendNewOrderAlert;
use App\Listeners\SendNewOrderWhatsAppNotification;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PromotionResolver::class);

        $this->app->singleton(WhatsAppServiceInterface::class, function () {
            $enabled    = (bool) config('services.whatsapp.notify_enabled', false);
            $instanceId = (string) config('services.whatsapp.green_api.instance_id', '');
            $token      = (string) config('services.whatsapp.green_api.token', '');

            if (! $enabled || $instanceId === '' || $token === '') {
                return new NullWhatsAppService;
            }

            return new GreenApiWhatsAppService(
                baseUrl: (string) config('services.whatsapp.green_api.base_url', 'https://api.green-api.com'),
                instanceId: $instanceId,
                token: $token,
                timeoutSeconds: (int) config('services.whatsapp.green_api.timeout', 15),
            );
        });
    }

    public function boot(): void
    {
        // ربط الأحداث بالمستمعين — التواصل بين الـ Contexts
        Event::listen(StockLevelChanged::class, CheckReorderPoint::class);
        Event::listen(LowStockDetected::class,  SendLowStockAlert::class);
        Event::listen(OrderPlaced::class,       SendNewOrderAlert::class);
        Event::listen(OrderPlaced::class,       SendNewOrderWhatsAppNotification::class);

        // GenerateInvoice معطّل: يتطلب spatie/laravel-pdf + Puppeteer.
        // الفاتورة متاحة كـ HTML على /orders/{order}/invoice
    }
}