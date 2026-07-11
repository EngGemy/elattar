@extends('layouts.storefront')

@section('title', 'سلة التسوق — عبد القادر العطّار')

@push('head-styles')
<style>
/* ── Cart page ── */
.cart-page{width:100%;max-width:100%;overflow-x:hidden}
.page-title{font-family:var(--font-thuluth);font-size:2rem;font-weight:400;padding:28px 0 20px}
.money{unicode-bidi:isolate;direction:ltr;display:inline-block}

/* Desktop */
.cart-d{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,340px);gap:28px;padding-bottom:60px;align-items:start}
.cart-d-table{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);overflow:hidden}
.cart-d-head,.cart-d-row{display:grid;grid-template-columns:minmax(0,2fr) minmax(0,1fr) minmax(0,1fr) 40px;gap:12px;padding:14px 18px;align-items:center}
.cart-d-head{border-bottom:1.5px solid var(--hair);font-family:var(--font-ui);font-size:.8rem;font-weight:600;color:var(--ink-soft)}
.cart-d-row{border-bottom:1px solid var(--hair)}
.cart-d-row:last-child{border-bottom:none}
.cart-d-info{display:flex;gap:12px;align-items:flex-start;min-width:0}
.cart-d-img{width:58px;height:58px;border-radius:9px;object-fit:cover;background:var(--parchment-2);flex-shrink:0}
.cart-d-img.ph{display:flex;align-items:center;justify-content:center;font-size:1.4rem}
.cart-d-name{font-weight:700;font-size:.94rem;line-height:1.45}
.cart-d-sub{font-size:.78rem;color:var(--ink-soft);margin-top:2px}
.cart-d-price{font-weight:700;color:var(--gold-deep);font-size:.92rem}
.cart-d-qty{display:flex;align-items:center;gap:5px;margin-top:8px}
.cart-d-qty button{width:32px;height:32px;border:1px solid var(--hair);border-radius:8px;background:var(--parchment);font-size:1rem;cursor:pointer}
.cart-d-qty input{width:52px;text-align:center;border:1px solid var(--hair);border-radius:8px;padding:5px;font-size:.9rem;font-weight:700}
.cart-d-del{background:none;border:none;color:#bbb;font-size:1.3rem;cursor:pointer;width:32px;height:32px;border-radius:8px}
.cart-d-del:hover{background:#fee2e2;color:var(--clay)}

.summary-box{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);padding:22px;position:sticky;top:96px}
.summary-box h3{font-family:var(--font-ui);font-size:1.05rem;font-weight:700;margin-bottom:14px}
.sum-line{display:flex;justify-content:space-between;align-items:center;gap:8px;padding:7px 0;border-bottom:1px solid var(--hair);font-size:.9rem}
.sum-line:last-of-type{border-bottom:none}
.sum-line span:first-child{color:var(--ink-soft)}
.sum-line.sum-grand{font-weight:800;font-size:1.05rem;padding-top:10px;margin-top:4px;border-top:2px solid var(--hair)}
.sum-line.sum-grand span:first-child{color:var(--ink)}
.coupon-row{display:flex;gap:8px;margin-top:14px}
.coupon-row input{flex:1;min-width:0;padding:10px 12px;border:1.5px solid var(--hair);border-radius:10px;font-size:.9rem;background:var(--parchment-2)}
.coupon-row button{padding:10px 16px;background:var(--ink);color:#fff;border:none;border-radius:10px;font-weight:700;cursor:pointer;white-space:nowrap}
.coupon-ok{display:flex;justify-content:space-between;align-items:center;gap:8px;background:#d1fae5;border:1px solid #6ee7b7;padding:10px 12px;border-radius:10px;font-size:.85rem;font-weight:600;color:#065f46;margin-top:12px}
.btn-checkout{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;margin-top:16px;padding:15px;background:var(--ink);color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;text-decoration:none}
.btn-checkout:hover{background:var(--gold-deep)}
.continue-shop{margin-top:14px}
.continue-shop .btn-outline{display:inline-flex}

.empty-cart{text-align:center;padding:56px 16px;color:var(--ink-soft)}
.empty-cart p{font-size:1.05rem;margin:14px 0}

/* Mobile — separate clean layout */
.cart-m{display:none;width:100%;max-width:100%;padding-bottom:calc(88px + env(safe-area-inset-bottom,0px))}
.cart-m-list{display:flex;flex-direction:column;gap:10px;width:100%}
.cart-m-item{background:var(--card);border:1px solid var(--hair);border-radius:14px;padding:12px;width:100%;max-width:100%;overflow:hidden;box-shadow:0 4px 16px -8px rgba(42,24,16,.1)}
.cart-m-top{display:flex;align-items:flex-start;gap:10px;width:100%;min-width:0}
.cart-m-img{width:52px;height:52px;border-radius:10px;object-fit:cover;background:var(--parchment-2);flex-shrink:0}
.cart-m-img.ph{display:flex;align-items:center;justify-content:center;font-size:1.3rem}
.cart-m-info{flex:1;min-width:0;overflow:hidden}
.cart-m-name{font-weight:700;font-size:.9rem;line-height:1.4;word-break:break-word}
.cart-m-unit{font-size:.74rem;color:var(--ink-soft);margin-top:2px}
.cart-m-del{flex-shrink:0;width:32px;height:32px;border:none;background:var(--parchment-2);color:#aaa;border-radius:8px;font-size:1.2rem;cursor:pointer;display:flex;align-items:center;justify-content:center}
.cart-m-del:active{background:#fee2e2;color:var(--clay)}
.cart-m-bottom{display:flex;align-items:center;justify-content:space-between;gap:8px;margin-top:10px;padding-top:10px;border-top:1px solid var(--hair);width:100%;min-width:0}
.cart-m-qty{display:flex;align-items:center;gap:4px;background:var(--parchment-2);border:1px solid var(--hair);border-radius:10px;padding:3px 4px;flex-shrink:1;min-width:0}
.cart-m-qty button{width:32px;height:32px;border:none;background:#fff;border-radius:8px;font-size:1.1rem;font-weight:700;cursor:pointer;flex-shrink:0;color:var(--ink)}
.cart-m-qty input{width:44px;min-width:0;border:none;background:transparent;text-align:center;font-size:16px;font-weight:700;padding:4px 0;-moz-appearance:textfield}
.cart-m-qty input::-webkit-outer-spin-button,.cart-m-qty input::-webkit-inner-spin-button{-webkit-appearance:none}
.cart-m-qty .u{font-size:.7rem;color:var(--ink-soft);padding-inline:2px;flex-shrink:0}
.cart-m-line{font-weight:800;font-size:.95rem;color:var(--gold-deep);white-space:nowrap;flex-shrink:0}

.cart-m-summary{background:var(--card);border:1px solid var(--hair);border-radius:14px;padding:14px;margin-top:14px;width:100%;max-width:100%}
.cart-m-summary h3{font-family:var(--font-ui);font-size:.92rem;font-weight:700;margin-bottom:10px}
.cart-m-summary .sum-line{font-size:.84rem;padding:6px 0}
.cart-m-summary .sum-line.sum-grand{font-size:.95rem}
.cart-m-summary .coupon-row{margin-top:10px}
.cart-m-summary .coupon-row input{font-size:16px;padding:11px 12px}
.cart-m-summary .coupon-row button{padding:11px 14px;font-size:.88rem}
.cart-m-continue{margin-top:12px;text-align:center}
.cart-m-continue .btn-outline{width:100%;justify-content:center;font-size:.88rem;padding:11px}

.cart-m-bar{
  display:none;position:fixed;inset-inline:0;bottom:0;z-index:55;width:100%;max-width:100vw;
  background:#fff;border-top:1px solid var(--hair);
  padding:10px 16px;padding-bottom:calc(10px + env(safe-area-inset-bottom,0px));
  box-shadow:0 -6px 24px -6px rgba(42,24,16,.12);
}
.cart-m-bar .row{display:flex;align-items:center;gap:10px;width:100%;max-width:100%}
.cart-m-bar .lbl{flex:1;min-width:0}
.cart-m-bar .lbl small{display:block;font-size:.68rem;color:var(--ink-soft)}
.cart-m-bar .lbl strong{font-size:1.1rem;color:var(--gold-deep)}
.cart-m-bar .btn-checkout{margin:0;flex:1;min-width:0;padding:13px 10px;font-size:.9rem;border-radius:11px}

@media(max-width:767px){
  .cart-d{display:none}
  .cart-m{display:block}
  .cart-m-bar{display:block}
  .page-title{font-size:1.35rem;padding:14px 0 12px}
}
</style>
@endpush

@section('content')
<div class="wrap cart-page">
  <h1 class="page-title">سلة التسوق @if(!empty($cart))<span style="font-size:.55em;color:var(--ink-soft);font-family:var(--font-ui)">({{ count($cart) }})</span>@endif</h1>

  @if(empty($cart))
  <div class="empty-cart">
    <span style="font-size:3rem">🛒</span>
    <p>سلتك فارغة! تصفّح منتجاتنا وأضف ما يعجبك.</p>
    <a href="{{ route('storefront.catalog') }}" class="btn-primary" style="display:inline-flex;margin-top:8px">تصفّح المنتجات</a>
  </div>
  @else

  @php
    $taxMinor = (int) round($total - ($total / 1.14));
    $subtotalFmt = number_format($subtotal / 100, 2);
    $taxFmt = number_format($taxMinor / 100, 2);
    $totalFmt = number_format($total / 100, 2);
  @endphp

  {{-- ══ MOBILE ══ --}}
  <div class="cart-m">
    <div class="cart-m-list">
      @foreach($cart as $line)
      @php
        $unitFmt = number_format($line['price_minor'] / 100, 2);
        $lineFmt = number_format($line['line_total_minor'] / 100, 2);
      @endphp
      <article class="cart-m-item">
        <div class="cart-m-top">
          @if($line['image'])
            <img src="{{ $line['image'] }}" alt="" class="cart-m-img">
          @else
            <div class="cart-m-img ph">🌿</div>
          @endif
          <div class="cart-m-info">
            <div class="cart-m-name">{{ $line['name'] }}</div>
            <div class="cart-m-unit">
              <span class="money">{{ $unitFmt }}</span> ج.م /
              {{ $line['is_weighted'] ? 'كجم' : $line['unit_label'] }}
            </div>
          </div>
          <form action="{{ route('storefront.cart.remove', $line['variant_id']) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="cart-m-del" aria-label="حذف">×</button>
          </form>
        </div>
        <div class="cart-m-bottom">
          <form action="{{ route('storefront.cart.update') }}" method="POST" class="cart-m-qty">
            @csrf
            <input type="hidden" name="variant_id" value="{{ $line['variant_id'] }}">
            <button type="submit" name="qty" value="{{ max(0, $line['qty'] - $line['step']) }}">−</button>
            <input type="number" name="qty" value="{{ $line['qty'] }}" step="{{ $line['step'] }}" onchange="this.form.submit()">
            <span class="u">{{ $line['is_weighted'] ? 'جم' : $line['unit_label'] }}</span>
            <button type="submit" name="qty" value="{{ $line['qty'] + $line['step'] }}">+</button>
          </form>
          <span class="cart-m-line"><span class="money">{{ $lineFmt }}</span> ج.م</span>
        </div>
      </article>
      @endforeach
    </div>

    <div class="cart-m-summary">
      <h3>ملخص الطلب</h3>
      <div class="sum-line"><span>المجموع الفرعي</span><span><span class="money">{{ $subtotalFmt }}</span> ج.م</span></div>
      @if($discountMinor > 0)
      <div class="sum-line"><span>خصم ({{ $coupon }})</span><span style="color:var(--olive)">− <span class="money">{{ number_format($discountMinor / 100, 2) }}</span> ج.م</span></div>
      @endif
      <div class="sum-line"><span>ض.ق.م 14%</span><span><span class="money">{{ $taxFmt }}</span> ج.م</span></div>
      <div class="sum-line sum-grand"><span>الإجمالي</span><strong><span class="money">{{ $totalFmt }}</span> ج.م</strong></div>

      @if($coupon)
      <div class="coupon-ok">
        <span>✓ {{ $coupon }}</span>
        <form action="{{ route('storefront.cart.coupon') }}" method="POST">
          @csrf<button type="submit" name="coupon" value="" style="background:none;border:none;color:#065f46;font-weight:700;cursor:pointer;font-size:.82rem">إزالة</button>
        </form>
      </div>
      @else
      <form action="{{ route('storefront.cart.coupon') }}" method="POST" class="coupon-row">
        @csrf
        <input type="text" name="coupon" placeholder="كوبون خصم" style="text-transform:uppercase">
        <button type="submit">تطبيق</button>
      </form>
      @endif
    </div>

    <div class="cart-m-continue">
      <a href="{{ route('storefront.catalog') }}" class="btn-outline">← مواصلة التسوق</a>
    </div>
  </div>

  <div class="cart-m-bar">
    <div class="row">
      <div class="lbl">
        <small>الإجمالي</small>
        <strong><span class="money">{{ $totalFmt }}</span> ج.م</strong>
      </div>
      <a href="{{ route('storefront.checkout') }}" class="btn-checkout">إتمام الطلب</a>
    </div>
  </div>

  {{-- ══ DESKTOP ══ --}}
  <div class="cart-d">
    <div>
      <div class="cart-d-table">
        <div class="cart-d-head">
          <span>المنتج</span><span>السعر</span><span>الإجمالي</span><span></span>
        </div>
        @foreach($cart as $line)
        @php
          $unitFmt = number_format($line['price_minor'] / 100, 2);
          $lineFmt = number_format($line['line_total_minor'] / 100, 2);
        @endphp
        <div class="cart-d-row">
          <div class="cart-d-info">
            @if($line['image'])
              <img src="{{ $line['image'] }}" alt="" class="cart-d-img">
            @else
              <div class="cart-d-img ph">🌿</div>
            @endif
            <div style="min-width:0">
              <div class="cart-d-name">{{ $line['name'] }}</div>
              <div class="cart-d-sub"><span class="money">{{ $unitFmt }}</span> ج.م / {{ $line['is_weighted'] ? 'كجم' : $line['unit_label'] }}</div>
              <form action="{{ route('storefront.cart.update') }}" method="POST" class="cart-d-qty">
                @csrf
                <input type="hidden" name="variant_id" value="{{ $line['variant_id'] }}">
                <button type="submit" name="qty" value="{{ max(0, $line['qty'] - $line['step']) }}">−</button>
                <input type="number" name="qty" value="{{ $line['qty'] }}" step="{{ $line['step'] }}" onchange="this.form.submit()">
                <button type="submit" name="qty" value="{{ $line['qty'] + $line['step'] }}">+</button>
                <span style="font-size:.78rem;color:var(--ink-soft)">{{ $line['is_weighted'] ? 'جم' : $line['unit_label'] }}</span>
              </form>
            </div>
          </div>
          <div class="cart-d-price"><span class="money">{{ $unitFmt }}</span> ج.م</div>
          <div class="cart-d-price"><span class="money">{{ $lineFmt }}</span> ج.م</div>
          <form action="{{ route('storefront.cart.remove', $line['variant_id']) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="cart-d-del">×</button>
          </form>
        </div>
        @endforeach
      </div>
      <div class="continue-shop">
        <a href="{{ route('storefront.catalog') }}" class="btn-outline">← مواصلة التسوق</a>
      </div>
    </div>

    <div class="summary-box">
      <h3>ملخص الطلب</h3>
      <div class="sum-line"><span>المجموع الفرعي</span><span><span class="money">{{ $subtotalFmt }}</span> ج.م</span></div>
      @if($discountMinor > 0)
      <div class="sum-line"><span>خصم ({{ $coupon }})</span><span style="color:var(--olive)">− <span class="money">{{ number_format($discountMinor / 100, 2) }}</span> ج.م</span></div>
      @endif
      <div class="sum-line"><span>ض.ق.م 14%</span><span><span class="money">{{ $taxFmt }}</span> ج.م</span></div>
      <div class="sum-line sum-grand"><span>الإجمالي</span><strong><span class="money">{{ $totalFmt }}</span> ج.م</strong></div>

      @if($coupon)
      <div class="coupon-ok">
        <span>✓ كوبون «{{ $coupon }}»</span>
        <form action="{{ route('storefront.cart.coupon') }}" method="POST">
          @csrf<button type="submit" name="coupon" value="" style="background:none;border:none;color:#065f46;font-weight:700;cursor:pointer">إزالة</button>
        </form>
      </div>
      @else
      <form action="{{ route('storefront.cart.coupon') }}" method="POST" class="coupon-row">
        @csrf
        <input type="text" name="coupon" placeholder="كوبون الخصم…" style="text-transform:uppercase">
        <button type="submit">تطبيق</button>
      </form>
      @endif

      <a href="{{ route('storefront.checkout') }}" class="btn-checkout">
        <svg width="17" height="17" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
        إتمام الطلب
      </a>
    </div>
  </div>

  @endif
</div>
@endsection
