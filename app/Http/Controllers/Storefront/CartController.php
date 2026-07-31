<?php

declare(strict_types=1);

namespace App\Http\Controllers\Storefront;

use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Inventory\Models\Warehouse;
use App\Domain\Pricing\Models\Coupon;
use App\Domain\Shared\ValueObjects\Money;
use App\Domain\Shared\ValueObjects\Quantity;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class CartController extends Controller
{
    public function index()
    {
        $sync     = $this->syncCartStock();
        $cart     = $sync['cart'];
        $coupon   = session('storefront_coupon');
        $subtotal = array_reduce($cart, fn ($c, $l) => $c + $l['line_total_minor'], 0);

        $discountMinor = 0;
        if ($coupon) {
            $couponObj = Coupon::where('code', $coupon)->first();
            if ($couponObj && $couponObj->isValidFor(Money::ofMinor((int) $subtotal))) {
                $discountMinor = $couponObj->discountFor(Money::ofMinor((int) $subtotal))->minor;
            } else {
                session()->forget('storefront_coupon');
                $coupon = null;
            }
        }

        $total = max(0, $subtotal - $discountMinor);

        $stockNotice = null;
        if ($sync['removed'] || $sync['adjusted']) {
            $stockNotice = $this->buildStockNotice($sync['removed'], $sync['adjusted']);
        }

        return view('storefront.cart', compact(
            'cart', 'subtotal', 'discountMinor', 'total', 'coupon', 'stockNotice'
        ));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'variant_id' => 'required|integer|exists:product_variants,id',
            'qty'        => 'required|numeric|min:0.001',
        ]);

        $variant     = ProductVariant::with('product')->findOrFail($data['variant_id']);
        $warehouseId = $this->defaultWarehouseId();
        $available   = $variant->availableAt($warehouseId);

        if (! $variant->is_active || $available <= 0) {
            return $this->cartResponse($request, false, 'هذا المنتج غير متاح للطلب حاليًا.');
        }

        $qty = $variant->normalizeOrderQty((float) $data['qty']);

        if (! $variant->isValidQuantity(Quantity::of($qty, $variant->unit))) {
            return $this->cartResponse(
                $request,
                false,
                'الكمية يجب أن تكون من مضاعفات ' . $variant->minOrderQty() . ' ' . $variant->unit->labelAr() . '.'
            );
        }

        $cart = session('storefront_cart', []);
        $key  = (string) $variant->id;
        $alreadyInCart = (float) ($cart[$key]['qty'] ?? 0);
        $room = $available - $alreadyInCart;

        if ($room <= 0) {
            return $this->cartResponse(
                $request,
                false,
                'وصلتَ للحد الأقصى المتاح من «' . $variant->product->name . '» في السلة.'
            );
        }

        if ($qty > $room) {
            $qty = $variant->normalizeOrderQty($room);
            if ($qty <= 0 || $qty > $room || ! $variant->isValidQuantity(Quantity::of($qty, $variant->unit))) {
                return $this->cartResponse(
                    $request,
                    false,
                    'الكمية المطلوبة غير متاحة حاليًا لـ «' . $variant->product->name . '».'
                );
            }
        }

        if (isset($cart[$key])) {
            $cart[$key]['qty'] = $alreadyInCart + $qty;
        } else {
            $effectiveMinor = $variant->effectivePrice()->minor;
            $originalMinor  = (int) $variant->getRawOriginal('price_minor');

            $cart[$key] = [
                'variant_id'       => $variant->id,
                'qty'              => $qty,
                'name'             => $variant->product->name,
                'sku'              => $variant->sku,
                'price_minor'      => $effectiveMinor,
                'compare_at_minor' => $originalMinor > $effectiveMinor ? $originalMinor : null,
                'unit'             => $variant->unit->value,
                'unit_label'       => $variant->unit->labelAr(),
                'step'             => (float) $variant->step,
                'is_weighted'      => $variant->unit->isFractional(),
                'image'            => $variant->product->getFirstMediaUrl('main', 'thumb') ?: null,
            ];
        }

        $cart[$key]['available']        = $available;
        $cart[$key]['line_total_minor'] = $this->lineTotal($cart[$key]);

        session(['storefront_cart' => $cart]);

        $cartCount = $this->cartBadgeCount($cart);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'         => true,
                'message'    => 'تم إضافة المنتج للسلة',
                'cart_count' => $cartCount,
                'line_qty'   => $cart[$key]['qty'],
                'line'       => $cart[$key],
            ]);
        }

        return back()->with('success', 'تم إضافة المنتج للسلة.');
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'variant_id' => 'required|integer',
            'qty'        => 'required|numeric|min:0',
        ]);

        $cart = session('storefront_cart', []);
        $key  = (string) $data['variant_id'];

        if (! isset($cart[$key])) {
            return $this->cartResponse($request, false, 'الصنف غير موجود في السلة.');
        }

        $variant = ProductVariant::with('product')->find($cart[$key]['variant_id']);

        if (! $variant || ! $variant->is_active) {
            unset($cart[$key]);
            session(['storefront_cart' => $cart]);

            return $this->cartResponse($request, true, 'تم حذف الصنف لأنه لم يعد متاحًا.', [
                'cart_count'     => $this->cartBadgeCount($cart),
                'removed'        => true,
                'subtotal_minor' => array_reduce($cart, fn ($c, $l) => $c + $l['line_total_minor'], 0),
            ]);
        }

        if ((float) $data['qty'] <= 0) {
            unset($cart[$key]);
            session(['storefront_cart' => $cart]);

            return $this->cartResponse($request, true, 'تم حذف الصنف.', [
                'cart_count'     => $this->cartBadgeCount($cart),
                'removed'        => true,
                'subtotal_minor' => array_reduce($cart, fn ($c, $l) => $c + $l['line_total_minor'], 0),
            ]);
        }

        $warehouseId = $this->defaultWarehouseId();
        $available   = $variant->availableAt($warehouseId);

        if ($available <= 0) {
            unset($cart[$key]);
            session(['storefront_cart' => $cart]);

            return $this->cartResponse($request, true, 'تم حذف «' . $variant->product->name . '» لأنه نفد من المخزون.', [
                'cart_count'     => $this->cartBadgeCount($cart),
                'removed'        => true,
                'subtotal_minor' => array_reduce($cart, fn ($c, $l) => $c + $l['line_total_minor'], 0),
            ]);
        }

        $qty = $variant->normalizeOrderQty((float) $data['qty']);

        if ($qty > $available) {
            $qty = $variant->normalizeOrderQty($available);
        }

        if ($qty <= 0 || ! $variant->isValidQuantity(Quantity::of($qty, $variant->unit)) || $qty > $available) {
            return $this->cartResponse(
                $request,
                false,
                'تعذّر تحديث الكمية — جرّب كمية أقل لـ «' . $variant->product->name . '».'
            );
        }

        $cart[$key]['qty']              = $qty;
        $cart[$key]['available']        = $available;
        $cart[$key]['line_total_minor'] = $this->lineTotal($cart[$key]);
        session(['storefront_cart' => $cart]);

        return $this->cartResponse($request, true, 'تم تحديث الكمية', [
            'cart_count'     => $this->cartBadgeCount($cart),
            'line'           => $cart[$key],
            'line_total_fmt' => number_format($cart[$key]['line_total_minor'] / 100, 2),
            'subtotal_minor' => array_reduce($cart, fn ($c, $l) => $c + $l['line_total_minor'], 0),
        ]);
    }

    public function remove(string $variantId)
    {
        $cart = session('storefront_cart', []);
        unset($cart[$variantId]);
        session(['storefront_cart' => $cart]);

        return back()->with('success', 'تم حذف الصنف.');
    }

    public function applyCoupon(Request $request)
    {
        $code = strtoupper(trim($request->input('coupon', '')));

        if (empty($code)) {
            session()->forget('storefront_coupon');

            return back()->with('success', 'تم إزالة الكوبون.');
        }

        $cart     = $this->loadCart();
        $subtotal = array_reduce($cart, fn ($c, $l) => $c + $l['line_total_minor'], 0);
        $coupon   = Coupon::where('code', $code)->first();

        if (! $coupon || ! $coupon->isValidFor(Money::ofMinor((int) $subtotal))) {
            return back()->with('error', 'الكوبون غير صالح أو لا يُطبَّق على المبلغ الحالي.');
        }

        session(['storefront_coupon' => $code]);

        return back()->with('success', "تم تطبيق كوبون الخصم «{$code}».");
    }

    /** تحميل السلة وتحديث أسعارها (مقاومة للتلاعب) */
    public function loadCart(): array
    {
        return $this->syncCartStock()['cart'];
    }

    /**
     * يحدّث الأسعار ويزيل/يعدّل الأصناف غير المتاحة قبل العرض أو الطلب.
     *
     * @return array{cart: array, removed: list<array{name: string, reason: string}>, adjusted: list<array{name: string}>}
     */
    public function syncCartStock(): array
    {
        $cart        = session('storefront_cart', []);
        $warehouseId = $this->defaultWarehouseId();
        $removed     = [];
        $adjusted    = [];

        foreach ($cart as $key => &$line) {
            $variant = ProductVariant::with('product')->find($line['variant_id'] ?? 0);

            if (! $variant || ! $variant->is_active) {
                $removed[] = [
                    'name'   => (string) ($line['name'] ?? 'صنف'),
                    'reason' => 'unavailable',
                ];
                unset($cart[$key]);
                continue;
            }

            $available = $variant->availableAt($warehouseId);
            $name      = $variant->product?->name ?? (string) ($line['name'] ?? 'صنف');

            if ($available <= 0) {
                $removed[] = ['name' => $name, 'reason' => 'out_of_stock'];
                unset($cart[$key]);
                continue;
            }

            $effectiveMinor           = $variant->effectivePrice()->minor;
            $originalMinor            = (int) $variant->getRawOriginal('price_minor');
            $line['name']             = $name;
            $line['price_minor']      = $effectiveMinor;
            $line['compare_at_minor'] = $originalMinor > $effectiveMinor ? $originalMinor : null;
            $line['available']        = $available;
            $line['is_weighted']      = $variant->unit->isFractional();
            $line['step']             = (float) $variant->step;
            $line['unit_label']       = $variant->unit->labelAr();

            $qty = (float) ($line['qty'] ?? 0);
            if ($qty > $available) {
                $newQty = $variant->normalizeOrderQty($available);
                if ($newQty <= 0
                    || $newQty > $available
                    || ! $variant->isValidQuantity(Quantity::of($newQty, $variant->unit))
                ) {
                    $removed[] = ['name' => $name, 'reason' => 'insufficient'];
                    unset($cart[$key]);
                    continue;
                }
                $adjusted[]  = ['name' => $name];
                $line['qty'] = $newQty;
            }

            $line['line_total_minor'] = $this->lineTotal($line);
        }
        unset($line);

        session(['storefront_cart' => $cart]);

        return [
            'cart'     => $cart,
            'removed'  => $removed,
            'adjusted' => $adjusted,
        ];
    }

    /**
     * @param  list<array{name: string, reason?: string}>  $removed
     * @param  list<array{name: string}>  $adjusted
     */
    public function buildStockNotice(array $removed, array $adjusted): array
    {
        $names = collect($removed)->pluck('name')
            ->merge(collect($adjusted)->pluck('name'))
            ->unique()
            ->values()
            ->all();

        $title = 'حدّثنا سلتك تلقائيًا';
        $body  = 'بعض الأصناف لم تعد متاحة بالكمية السابقة، فعدلنا السلة عشان تقدر تكمّل طلبك بسهولة.';

        if ($removed && ! $adjusted) {
            $title = 'أصناف غير متاحة';
            $body  = 'شلنا من السلة المنتجات اللي نفدت أو بطلت متاحة، عشان متعملش طلب ناقص.';
        } elseif ($adjusted && ! $removed) {
            $title = 'تم تعديل الكميات';
            $body  = 'خفضنا كمية بعض الأصناف لتطابق المتاح حاليًا.';
        }

        return [
            'type'  => 'stock',
            'title' => $title,
            'body'  => $body,
            'items' => $names,
        ];
    }

    private function defaultWarehouseId(): int
    {
        return (int) (Warehouse::where('is_default', true)->value('id') ?? 1);
    }

    private function lineTotal(array $line): int
    {
        return ! empty($line['is_weighted'])
            ? (int) round($line['price_minor'] * $line['qty'] / 1000)
            : (int) round($line['price_minor'] * $line['qty']);
    }

    private function cartBadgeCount(array $cart): int
    {
        $n = 0;
        foreach ($cart as $line) {
            if (! empty($line['is_weighted'])) {
                $n += 1;
            } else {
                $n += max(1, (int) round((float) ($line['qty'] ?? 1)));
            }
        }

        return $n;
    }

    private function cartResponse(Request $request, bool $ok, string $message, array $extra = [])
    {
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(array_merge([
                'ok'      => $ok,
                'message' => $message,
            ], $extra), $ok ? 200 : 422);
        }

        return $ok
            ? back()->with('success', $message)
            : back()->with('error', $message);
    }
}
