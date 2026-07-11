@extends('layouts.storefront')

@section('title', 'سلة التسوق — عبد القادر العطّار')

@push('head-styles')
<style>
.page-title{font-family:var(--font-thuluth);font-size:2rem;font-weight:400;padding:28px 0 20px}
.cart-page{overflow-x:clip;max-width:100%}
.cart-layout{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,360px);gap:28px;padding-bottom:60px;align-items:start}
.cart-main-col,.cart-side-col{min-width:0}

/* Items — desktop table */
.cart-table{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);overflow:hidden}
.cart-head{display:grid;grid-template-columns:minmax(0,2fr) minmax(0,1fr) minmax(0,1fr) auto;gap:12px;padding:14px 20px;
  border-bottom:1.5px solid var(--hair);font-family:var(--font-ui);font-size:.82rem;font-weight:600;color:var(--ink-soft)}
.cart-row{display:grid;grid-template-columns:minmax(0,2fr) minmax(0,1fr) minmax(0,1fr) auto;gap:12px;padding:16px 20px;
  border-bottom:1px solid var(--hair);align-items:center}
.cart-row:last-child{border-bottom:none}
.item-info{display:flex;align-items:flex-start;gap:12px;min-width:0}
.item-img{width:60px;height:60px;border-radius:9px;object-fit:cover;background:var(--parchment-2);flex-shrink:0}
.item-img.placeholder{display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:var(--hair)}
.item-meta{flex:1;min-width:0}
.item-name{font-weight:700;font-size:.96rem;line-height:1.45;word-break:break-word}
.item-sub{font-size:.8rem;color:var(--ink-soft);margin-top:2px}
.item-price{font-family:var(--font-naskh);color:var(--gold-deep);font-weight:700;font-size:.95rem;white-space:nowrap}

.inline-qty{display:flex;align-items:center;gap:6px;flex-wrap:wrap;margin-top:8px;max-width:100%}
.inline-qty button{width:36px;height:36px;border:1.5px solid var(--hair);border-radius:9px;
  background:var(--parchment-2);font-size:1.1rem;cursor:pointer;transition:.15s;flex-shrink:0}
.inline-qty button:hover{border-color:var(--gold)}
.inline-qty input{width:56px;min-width:0;text-align:center;border:1.5px solid var(--hair);border-radius:9px;
  padding:6px 2px;font-size:16px;font-weight:700;background:var(--card);color:var(--ink)}
.inline-qty .qty-unit{font-size:.78rem;color:var(--ink-soft);flex-shrink:0}

.del-btn{background:none;border:none;color:#aaa;cursor:pointer;font-size:1.4rem;
  width:36px;height:36px;display:flex;align-items:center;justify-content:center;
  border-radius:9px;transition:.15s;flex-shrink:0}
.del-btn:hover{background:#fee2e2;color:var(--clay)}

/* Mobile item card extras */
.cart-item-mobile{display:none}
.cart-item-top{display:flex;align-items:flex-start;gap:12px;min-width:0}
.cart-item-footer{display:flex;align-items:center;justify-content:space-between;gap:12px;
  padding-top:12px;margin-top:12px;border-top:1px solid var(--hair)}
.cart-item-total{font-family:var(--font-naskh);font-weight:800;font-size:1rem;color:var(--gold-deep);white-space:nowrap}

/* Summary */
.summary-card{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);padding:24px;position:sticky;top:100px}
.summary-card h3{font-family:var(--font-ui);font-size:1.1rem;font-weight:700;margin-bottom:18px;color:var(--ink)}
.summary-row{display:flex;justify-content:space-between;align-items:center;gap:10px;padding:8px 0;border-bottom:1px solid var(--hair)}
.summary-row:last-of-type{border-bottom:none;font-weight:800;font-size:1.1rem;padding-top:14px}
.summary-row span:first-child{color:var(--ink-soft);min-width:0}
.summary-row span:last-child,.summary-row strong{flex-shrink:0;white-space:nowrap}
.discount-val{color:var(--olive);font-weight:700}

.coupon-form{display:flex;gap:8px;margin:16px 0;min-width:0}
.coupon-form input{flex:1;min-width:0;padding:10px 14px;border:1.5px solid var(--hair);border-radius:10px;
  font-size:16px;background:var(--parchment-2);color:var(--ink);outline:none}
.coupon-form input:focus{border-color:var(--gold)}
.coupon-form button{padding:10px 16px;background:var(--ink);color:var(--parchment);border:none;
  border-radius:10px;font-weight:700;cursor:pointer;transition:.2s;white-space:nowrap;flex-shrink:0}
.coupon-form button:hover{background:var(--gold-deep)}
.coupon-applied{display:flex;align-items:center;justify-content:space-between;gap:10px;
  background:#d1fae5;border:1px solid #6ee7b7;padding:10px 14px;border-radius:10px;
  font-size:.88rem;font-weight:600;color:#065f46;margin:10px 0;min-width:0}
.coupon-applied span{min-width:0;word-break:break-word}

.checkout-btn{width:100%;margin-top:18px;padding:16px;background:var(--ink);color:var(--parchment);
  border:none;border-radius:13px;font-size:1.05rem;font-weight:700;cursor:pointer;transition:.22s;text-decoration:none;
  display:flex;align-items:center;justify-content:center;gap:10px}
.checkout-btn:hover{background:var(--gold-deep)}

.continue-link{margin-top:16px}
.continue-link .btn-outline{display:inline-flex;max-width:100%}

.empty-cart{text-align:center;padding:60px 20px;color:var(--ink-soft)}
.empty-cart p{font-size:1.1rem;margin:16px 0}

/* Mobile sticky bar */
.cart-mobile-bar{
  display:none;position:fixed;left:0;right:0;bottom:0;z-index:55;
  background:rgba(255,255,255,.97);backdrop-filter:blur(12px);
  border-top:1px solid var(--hair);
  padding:12px clamp(16px,4vw,24px);
  padding-bottom:calc(12px + env(safe-area-inset-bottom, 0px));
  box-shadow:0 -8px 28px -8px rgba(42,24,16,.12);
}
.cart-mobile-bar .inner{display:flex;align-items:center;gap:12px;max-width:1200px;margin:0 auto}
.cart-mobile-bar .total{flex:1;min-width:0}
.cart-mobile-bar .total small{display:block;font-size:.72rem;color:var(--ink-soft);margin-bottom:2px}
.cart-mobile-bar .total strong{font-family:var(--font-naskh);font-size:1.15rem;color:var(--gold-deep)}
.cart-mobile-bar .checkout-btn{margin:0;width:auto;flex:1;min-width:0;padding:14px 16px;font-size:.95rem}

@media(max-width:780px){
  .cart-layout{display:flex;flex-direction:column;gap:14px;padding-bottom:calc(96px + env(safe-area-inset-bottom, 0px))}
  .cart-side-col{order:-1}
  .summary-card{position:static;padding:16px;border-radius:14px}
  .summary-card h3{font-size:1rem;margin-bottom:14px}
  .page-title{font-size:1.45rem;padding:16px 0 10px;line-height:1.3}
  .cart-mobile-bar{display:block}

  .cart-head{display:none}
  .cart-row{display:block;padding:0;border-bottom:none}
  .cart-table{background:transparent;border:none;box-shadow:none;display:flex;flex-direction:column;gap:12px}
  .cart-row > .item-price,.cart-row > .item-total{display:none}
  .cart-row > form:last-child{display:none}

  .cart-item-card{
    background:var(--card);border:1px solid var(--hair);border-radius:14px;
    padding:14px;box-shadow:var(--shadow);overflow:hidden;
  }
  .cart-item-mobile{display:block}
  .cart-desktop-only{display:none!important}
  .coupon-form{flex-direction:column}
  .coupon-form button{width:100%}
}

@media(min-width:781px){
  .cart-item-mobile{display:none!important}
}
</style>
@endpush

@section('content')
<div class="wrap cart-page">
  <h1 class="page-title">سلة التسوق</h1>

  @if(empty($cart))
  <div class="empty-cart">
    <span style="font-size:3.5rem">🛒</span>
    <p>سلتك فارغة! تصفّح منتجاتنا وأضف ما يعجبك.</p>
    <a href="{{ route('storefront.catalog') }}" class="btn-primary" style="display:inline-flex;margin-top:8px">
      تصفّح المنتجات
    </a>
  </div>
  @else
  <div class="cart-layout">

    <div class="cart-main-col">
      <div class="cart-table">
        <div class="cart-head cart-desktop-only">
          <span>المنتج</span>
          <span class="col-price">السعر</span>
          <span class="col-total">الإجمالي</span>
          <span></span>
        </div>

        @foreach($cart as $key => $line)
        @php
          $priceFmt = number_format($line['price_minor'] / 100, 2) . ' ج.م';
          $lineFmt = number_format($line['line_total_minor'] / 100, 2) . ' ج.م';
        @endphp
        <div class="cart-row">
          <div class="cart-item-card cart-item-mobile">
            <div class="cart-item-top">
              @if($line['image'])
                <img src="{{ $line['image'] }}" alt="{{ $line['name'] }}" class="item-img">
              @else
                <div class="item-img placeholder">🌿</div>
              @endif
              <div class="item-meta">
                <div class="item-name">{{ $line['name'] }}</div>
                <div class="item-sub">
                  {{ $priceFmt }} / {{ $line['is_weighted'] ? 'كجم' : $line['unit_label'] }}
                </div>
              </div>
              <form action="{{ route('storefront.cart.remove', $line['variant_id']) }}" method="POST">
                @csrf @method('DELETE')
                <button type="submit" class="del-btn" title="حذف" aria-label="حذف {{ $line['name'] }}">×</button>
              </form>
            </div>

            <div class="cart-item-footer">
              <form action="{{ route('storefront.cart.update') }}" method="POST" class="inline-qty">
                @csrf
                <input type="hidden" name="variant_id" value="{{ $line['variant_id'] }}">
                <button type="submit" name="qty" value="{{ max(0, $line['qty'] - $line['step']) }}" aria-label="تقليل">−</button>
                <input type="number" name="qty" value="{{ $line['qty'] }}" step="{{ $line['step'] }}"
                       onchange="this.form.submit()" aria-label="الكمية">
                <button type="submit" name="qty" value="{{ $line['qty'] + $line['step'] }}" aria-label="زيادة">+</button>
                <span class="qty-unit">{{ $line['is_weighted'] ? 'جم' : $line['unit_label'] }}</span>
              </form>
              <div class="cart-item-total">{{ $lineFmt }}</div>
            </div>
          </div>

          {{-- Desktop columns --}}
          <div class="item-info cart-desktop-only">
            @if($line['image'])
              <img src="{{ $line['image'] }}" alt="{{ $line['name'] }}" class="item-img">
            @else
              <div class="item-img placeholder">🌿</div>
            @endif
            <div class="item-meta">
              <div class="item-name">{{ $line['name'] }}</div>
              <div class="item-sub">
                {{ $priceFmt }} / {{ $line['is_weighted'] ? 'كجم' : $line['unit_label'] }}
              </div>
              <form action="{{ route('storefront.cart.update') }}" method="POST" class="inline-qty">
                @csrf
                <input type="hidden" name="variant_id" value="{{ $line['variant_id'] }}">
                <button type="submit" name="qty" value="{{ max(0, $line['qty'] - $line['step']) }}">−</button>
                <input type="number" name="qty" value="{{ $line['qty'] }}" step="{{ $line['step'] }}"
                       onchange="this.form.submit()">
                <button type="submit" name="qty" value="{{ $line['qty'] + $line['step'] }}">+</button>
                <span class="qty-unit">{{ $line['is_weighted'] ? 'جم' : $line['unit_label'] }}</span>
              </form>
            </div>
          </div>
          <div class="item-price cart-desktop-only">{{ $priceFmt }}</div>
          <div class="item-price item-total cart-desktop-only">{{ $lineFmt }}</div>
          <form action="{{ route('storefront.cart.remove', $line['variant_id']) }}" method="POST" class="cart-desktop-only">
            @csrf @method('DELETE')
            <button type="submit" class="del-btn" title="حذف">×</button>
          </form>
        </div>
        @endforeach
      </div>

      <div class="continue-link">
        <a href="{{ route('storefront.catalog') }}" class="btn-outline">← مواصلة التسوق</a>
      </div>
    </div>

    <div class="cart-side-col">
      <div class="summary-card">
        <h3>ملخص الطلب</h3>

        <div class="summary-row">
          <span>المجموع الفرعي</span>
          <span>{{ number_format($subtotal / 100, 2) }} ج.م</span>
        </div>

        @if($discountMinor > 0)
        <div class="summary-row">
          <span>خصم ({{ $coupon }})</span>
          <span class="discount-val">− {{ number_format($discountMinor / 100, 2) }} ج.م</span>
        </div>
        @endif

        @php $taxMinor = (int) round($total - ($total / 1.14)); @endphp
        <div class="summary-row">
          <span>ض.ق.م 14%</span>
          <span>{{ number_format($taxMinor / 100, 2) }} ج.م</span>
        </div>

        <div class="summary-row">
          <span>الإجمالي</span>
          <strong>{{ number_format($total / 100, 2) }} ج.م</strong>
        </div>

        @if($coupon)
        <div class="coupon-applied">
          <span>✓ كوبون «{{ $coupon }}»</span>
          <form action="{{ route('storefront.cart.coupon') }}" method="POST">
            @csrf
            <button type="submit" name="coupon" value="" style="background:none;border:none;color:#065f46;cursor:pointer;font-weight:700;font-size:.85rem;white-space:nowrap">إزالة</button>
          </form>
        </div>
        @else
        <form action="{{ route('storefront.cart.coupon') }}" method="POST" class="coupon-form">
          @csrf
          <input type="text" name="coupon" placeholder="كوبون الخصم…" style="text-transform:uppercase">
          <button type="submit">تطبيق</button>
        </form>
        @endif

        <a href="{{ route('storefront.checkout') }}" class="checkout-btn cart-desktop-only">
          <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
          </svg>
          إتمام الطلب
        </a>
      </div>
    </div>
  </div>

  <div class="cart-mobile-bar">
    <div class="inner">
      <div class="total">
        <small>الإجمالي</small>
        <strong>{{ number_format($total / 100, 2) }} ج.م</strong>
      </div>
      <a href="{{ route('storefront.checkout') }}" class="checkout-btn">إتمام الطلب</a>
    </div>
  </div>
  @endif
</div>
@endsection
