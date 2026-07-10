<?php

declare(strict_types=1);

namespace App\Providers;

use App\Domain\Inventory\Events\LowStockDetected;
use App\Domain\Inventory\Events\StockLevelChanged;
use App\Domain\Sales\Events\OrderPlaced;
use App\Listeners\CheckReorderPoint;
use App\Listeners\SendLowStockAlert;
use App\Listeners\SendNewOrderAlert;
use App\Domain\Pricing\Services\PromotionResolver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class DomainServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(PromotionResolver::class);
    }

    public function boot(): void
    {
        // ربط الأحداث بالمستمعين — التواصل بين الـ Contexts
        Event::listen(StockLevelChanged::class, CheckReorderPoint::class);
        Event::listen(LowStockDetected::class,  SendLowStockAlert::class);
        Event::listen(OrderPlaced::class,       SendNewOrderAlert::class);

        // GenerateInvoice معطّل: يتطلب spatie/laravel-pdf + Puppeteer.
        // الفاتورة متاحة كـ HTML على /orders/{order}/invoice
    }
}