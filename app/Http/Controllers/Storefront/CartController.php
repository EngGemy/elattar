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
        $cart    = $this->loadCart();
        $coupon  = session('storefront_coupon');
        $subtotal = array_reduce($cart, fn ($c, $l) => $c + $l['line_total_minor'], 0);

        $discountMinor = 0;
        $couponObj     = null;
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

        return view('storefront.cart', compact('cart', 'subtotal', 'discountMinor', 'total', 'coupon'));
    }

    public function add(Request $request)
    {
        $data = $request->validate([
            'variant_id' => 'required|integer|exists:product_variants,id',
            'qty'        => 'required|numeric|min:0.001',
        ]);

        $variant     = ProductVariant::with('product')->findOrFail($data['variant_id']);
        $warehouseId = (int) (Warehouse::where('is_default', true)->value('id') ?? 1);
        $available   = $variant->availableAt($warehouseId);

        if ($available <= 0) {
            return $this->cartResponse($request, false, 'المنتج نفد من المخزون.');
        }

        $qty  = $variant->normalizeOrderQty((float) $data['qty']);

        if (! $variant->isValidQuantity(Quantity::of($qty, $variant->unit))) {
            return $this->cartResponse(
                $request,
                false,
                'الكمية يجب أن تكون من مضاعفات ' . $variant->minOrderQty() . ' ' . $variant->unit->labelAr() . '.'
            );
        }

        $cart = session('storefront_cart', []);
        $key  = (string) $variant->id;

        if (isset($cart[$key])) {
            $newQty = $cart[$key]['qty'] + $qty;
            if ($newQty > $available) {
                return $this->cartResponse($request, false, 'الكمية المطلوبة تتجاوز المتاح.');
            }
            $cart[$key]['qty'] = $newQty;
        } else {
            $effectiveMinor = $variant->effectivePrice()->minor;
            $originalMinor  = (int) $variant->getRawOriginal('price_minor');

            $cart[$key] = [
                'variant_id'        => $variant->id,
                'qty'               => $qty,
                'name'              => $variant->product->name,
                'sku'               => $variant->sku,
                'price_minor'       => $effectiveMinor,
                'compare_at_minor'  => $originalMinor > $effectiveMinor ? $originalMinor : null,
                'unit'              => $variant->unit->value,
                'unit_label'        => $variant->unit->labelAr(),
                'step'              => (float) $variant->step,
                'is_weighted'       => $variant->unit->isFractional(),
                'image'             => $variant->product->getFirstMediaUrl('main', 'thumb') ?: null,
            ];
        }

        $cart[$key]['line_total_minor'] = $this->lineTotal($cart[$key]);

        session(['storefront_cart' => $cart]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'         => true,
                'message'    => 'تم إضافة المنتج للسلة',
                'cart_count' => count($cart),
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
            return back();
        }

        $variant = ProductVariant::find($cart[$key]['variant_id']);

        if (! $variant) {
            unset($cart[$key]);
            session(['storefront_cart' => $cart]);

            return back();
        }

        if ((float) $data['qty'] <= 0) {
            unset($cart[$key]);
        } else {
            $qty = $variant->normalizeOrderQty((float) $data['qty']);

            if (! $variant->isValidQuantity(Quantity::of($qty, $variant->unit))) {
                return back()->with('error', 'الكمية غير صالحة — الحد الأدنى ' . $variant->minOrderQty() . ' ' . $variant->unit->labelAr());
            }

            $warehouseId = (int) (Warehouse::where('is_default', true)->value('id') ?? 1);
            if ($qty > $variant->availableAt($warehouseId)) {
                return back()->with('error', 'الكمية تتجاوز المتوفر في المخزون.');
            }

            $cart[$key]['qty']              = $qty;
            $cart[$key]['line_total_minor'] = $this->lineTotal($cart[$key]);
        }

        session(['storefront_cart' => $cart]);

        return back();
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
        $cart = session('storefront_cart', []);

        foreach ($cart as $key => &$line) {
            $variant = ProductVariant::find($line['variant_id']);
            if (! $variant || ! $variant->is_active) {
                unset($cart[$key]);
                continue;
            }
            // تحديث السعر من قاعدة البيانات دائمًا (مع العروض)
            $effectiveMinor          = $variant->effectivePrice()->minor;
            $originalMinor           = (int) $variant->getRawOriginal('price_minor');
            $line['price_minor']     = $effectiveMinor;
            $line['compare_at_minor'] = $originalMinor > $effectiveMinor ? $originalMinor : null;
            $line['line_total_minor'] = $this->lineTotal($line);
        }
        unset($line);

        session(['storefront_cart' => $cart]);

        return $cart;
    }

    private function lineTotal(array $line): int
    {
        return $line['is_weighted']
            ? (int) round($line['price_minor'] * $line['qty'] / 1000)
            : (int) round($line['price_minor'] * $line['qty']);
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
