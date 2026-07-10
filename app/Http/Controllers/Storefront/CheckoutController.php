<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Domain\Crm\Models\Customer;
use App\Domain\Inventory\Exceptions\InsufficientStockException;
use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Sales\Actions\PlaceOrderAction;
use App\Domain\Sales\Exceptions\InvalidOrderException;
use App\Domain\Shared\Enums\SalesChannel;
use App\Support\StorefrontCheckout;
use App\Support\StorefrontWhatsApp;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    public function index()
    {
        $cart = app(CartController::class)->loadCart();

        if (empty($cart)) {
            return redirect()->route('storefront.cart')->with('error', 'السلة فارغة.');
        }

        $subtotal      = array_reduce($cart, fn ($c, $l) => $c + $l['line_total_minor'], 0);
        $coupon        = session('storefront_coupon');
        $discountMinor = 0;

        if ($coupon) {
            $couponObj = \App\Domain\Pricing\Models\Coupon::where('code', $coupon)->first();
            if ($couponObj) {
                $discountMinor = $couponObj->discountFor(
                    \App\Domain\Shared\ValueObjects\Money::ofMinor((int) $subtotal)
                )->minor;
            }
        }

        $total = max(0, $subtotal - $discountMinor);

        $payment = [
            'instapay'      => StorefrontCheckout::instapayNumber(),
            'vodafone_cash' => StorefrontCheckout::vodafoneCashNumber(),
        ];

        return view('storefront.checkout', compact(
            'cart', 'subtotal', 'discountMinor', 'total', 'coupon', 'payment'
        ));
    }

    public function store(Request $request, PlaceOrderAction $placeOrder)
    {
        $data = $request->validate([
            'name'           => 'required|string|max:120',
            'phone'          => 'required|string|max:20',
            'city'           => ['required', 'string', Rule::in(StorefrontCheckout::cities())],
            'address'        => 'required|string|max:200',
            'payment_method' => 'required|in:cod,instapay,vodafone_cash',
            'notes'          => 'nullable|string|max:500',
        ]);

        $cart = app(CartController::class)->loadCart();

        if (empty($cart)) {
            return redirect()->route('storefront.cart')->with('error', 'السلة فارغة.');
        }

        $customer = Customer::firstOrCreate(
            ['phone' => $data['phone']],
            ['name' => $data['name'], 'is_active' => true]
        );

        if ($customer->wasRecentlyCreated || empty($customer->name)) {
            $customer->update(['name' => $data['name']]);
        }

        $items = array_values(array_map(fn ($line) => [
            'variant_id' => $line['variant_id'],
            'qty'        => $line['qty'],
        ], $cart));

        $shippingAddress = [
            'recipient_name' => $data['name'],
            'phone'          => $data['phone'],
            'governorate'    => StorefrontCheckout::governorate(),
            'city'           => $data['city'],
            'street'         => $data['address'],
            'building'       => '',
            'payment_method' => $data['payment_method'],
        ];

        $orderNotes = trim(implode("\n", array_filter([
            $data['notes'] ?? null,
            'طريقة الدفع: ' . StorefrontCheckout::paymentLabel($data['payment_method']),
            ($num = StorefrontCheckout::paymentNumber($data['payment_method']))
                ? 'رقم التحويل: ' . $num
                : null,
        ])));

        try {
            $order = $placeOrder->execute(
                items:          $items,
                warehouseId:    (int) (Warehouse::where('is_default', true)->value('id') ?? 1),
                customerId:     $customer->id,
                channel:        SalesChannel::Online,
                shippingAddress: $shippingAddress,
                couponCode:     session('storefront_coupon'),
                idempotencyKey: (string) Str::uuid(),
                notes:          $orderNotes ?: null,
            );

            session()->forget(['storefront_cart', 'storefront_coupon']);

            $waUrl = StorefrontWhatsApp::notifyShopUrl($order);

            return redirect()->route('storefront.track', $order->number)
                ->with('success', "تم إرسال طلبك بنجاح! رقم الطلب: {$order->number}")
                ->with('whatsapp_notify', $waUrl);
        } catch (InsufficientStockException $e) {
            return back()->withInput()->with('error', 'بعض الأصناف غير متاحة بالكمية المطلوبة. راجع سلتك وحاول مجددًا.');
        } catch (InvalidOrderException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        }
    }
}
