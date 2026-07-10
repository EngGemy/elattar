<?php

declare(strict_types=1);

use App\Domain\Sales\Models\Order;
use App\Domain\Sales\Actions\IssueInvoiceAction;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\OfferController;
use App\Http\Controllers\Storefront\OrderTrackController;
use App\Http\Controllers\Storefront\StorefrontController;
use Illuminate\Support\Facades\Route;

// ── المتجر الإلكتروني (عام — بلا تسجيل دخول)
Route::get('/',                    [StorefrontController::class, 'home'])->name('storefront.home');
Route::get('/products',            [StorefrontController::class, 'catalog'])->name('storefront.catalog');
Route::get('/products/{slug}',     [StorefrontController::class, 'product'])->name('storefront.product');
Route::get('/offers',              [OfferController::class, 'index'])->name('storefront.offers');

Route::get('/cart',                [CartController::class, 'index'])->name('storefront.cart');
Route::post('/cart/add',           [CartController::class, 'add'])->name('storefront.cart.add');
Route::post('/cart/update',        [CartController::class, 'update'])->name('storefront.cart.update');
Route::delete('/cart/{variantId}', [CartController::class, 'remove'])->name('storefront.cart.remove');
Route::post('/cart/coupon',        [CartController::class, 'applyCoupon'])->name('storefront.cart.coupon');

Route::get('/checkout',            [CheckoutController::class, 'index'])->name('storefront.checkout');
Route::post('/checkout',           [CheckoutController::class, 'store'])->name('storefront.checkout.store');

Route::get('/track',               [OrderTrackController::class, 'lookup'])->name('storefront.track.lookup');
Route::post('/track',              [OrderTrackController::class, 'search'])->name('storefront.track.search');
Route::get('/orders/{number}/track', [OrderTrackController::class, 'show'])->name('storefront.track');

// ── لوحة التحكم
Route::middleware('auth')->group(function () {

    Route::get('/orders/{order}/invoice', function (Order $order) {
        $order->load('lines', 'customer', 'tenders');
        $invoice = app(IssueInvoiceAction::class)->execute($order);
        return view('pdf.invoice', compact('order', 'invoice'));
    })->name('orders.invoice');

    Route::get('/orders/{order}/receipt', function (Order $order) {
        $order->load('lines', 'customer', 'tenders', 'creator');
        return view('pdf.receipt', compact('order'));
    })->name('orders.receipt');
});
