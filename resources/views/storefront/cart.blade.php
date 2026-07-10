@extends('layouts.storefront')

@section('title', 'سلة التسوق — عبد القادر العطّار')

@push('head-styles')
<style>
.page-title{font-family:var(--font-thuluth);font-size:2rem;font-weight:400;padding:28px 0 20px}
.cart-layout{display:grid;grid-template-columns:1fr 360px;gap:28px;padding-bottom:60px;align-items:start}
@media(max-width:780px){
  .cart-layout{display:flex;flex-direction:column;gap:16px}
  .cart-layout .summary-card{order:-1;position:static;top:auto}
  .page-title{font-size:1.55rem;padding:18px 0 14px}
}

/* Items table */
.cart-table{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);overflow:hidden}
.cart-head{display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:12px;padding:14px 20px;
  border-bottom:1.5px solid var(--hair);font-family:'Reem Kufi';font-size:.82rem;font-weight:600;color:var(--ink-soft)}
.cart-row{display:grid;grid-template-columns:2fr 1fr 1fr auto;gap:12px;padding:16px 20px;
  border-bottom:1px solid var(--hair);align-items:center}
.cart-row:last-child{border-bottom:none}
.item-info{display:flex;align-items:center;gap:12px}
.item-img{width:60px;height:60px;border-radius:9px;object-fit:cover;background:var(--parchment-2);flex-shrink:0}
.item-img.placeholder{display:flex;align-items:center;justify-content:center;font-size:1.5rem;color:var(--hair)}
.item-name{font-weight:700;font-size:.96rem}
.item-sub{font-size:.8rem;color:var(--ink-soft);margin-top:2px}
.item-price{font-family:'Reem Kufi';color:var(--gold-deep);font-weight:700;font-size:.95rem}

/* Qty control inline */
.inline-qty{display:flex;align-items:center;gap:6px}
.inline-qty button{width:30px;height:30px;border:1.5px solid var(--hair);border-radius:7px;
  background:var(--parchment-2);font-size:1rem;cursor:pointer;transition:.15s}
.inline-qty button:hover{border-color:var(--gold)}
.inline-qty input{width:54px;text-align:center;border:1.5px solid var(--hair);border-radius:7px;
  padding:5px 2px;font-size:.9rem;font-weight:700;background:var(--card);color:var(--ink)}

.del-btn{background:none;border:none;color:#aaa;cursor:pointer;font-size:1.3rem;
  width:32px;height:32px;display:flex;align-items:center;justify-content:center;
  border-radius:7px;transition:.15s}
.del-btn:hover{background:#fee2e2;color:var(--clay)}

/* Summary card */
.summary-card{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);padding:24px;position:sticky;top:100px}
.summary-card h3{font-family:'Reem Kufi';font-size:1.1rem;font-weight:700;margin-bottom:18px;color:var(--ink)}
.summary-row{display:flex;justify-content:space-between;align-items:center;padding:8px 0;border-bottom:1px solid var(--hair)}
.summary-row:last-of-type{border-bottom:none;font-weight:800;font-size:1.1rem;padding-top:14px}
.summary-row span:first-child{color:var(--ink-soft)}
.discount-val{color:var(--olive);font-weight:700}

/* Coupon */
.coupon-form{display:flex;gap:8px;margin:16px 0}
.coupon-form input{flex:1;padding:10px 14px;border:1.5px solid var(--hair);border-radius:10px;
  font-size:.9rem;background:var(--parchment-2);color:var(--ink);outline:none}
.coupon-form input:focus{border-color:var(--gold)}
.coupon-form button{padding:10px 16px;background:var(--ink);color:var(--parchment);border:none;
  border-radius:10px;font-weight:700;cursor:pointer;transition:.2s;white-space:nowrap}
.coupon-form button:hover{background:var(--gold-deep)}
.coupon-applied{display:flex;align-items:center;justify-content:space-between;
  background:#d1fae5;border:1px solid #6ee7b7;padding:10px 14px;border-radius:10px;
  font-size:.88rem;font-weight:600;color:#065f46;margin:10px 0}

.checkout-btn{width:100%;margin-top:18px;padding:16px;background:var(--ink);color:var(--parchment);
  border:none;border-radius:13px;font-size:1.05rem;font-weight:700;cursor:pointer;transition:.22s;text-decoration:none;
  display:flex;align-items:center;justify-content:center;gap:10px}
.checkout-btn:hover{background:var(--gold-deep)}

.empty-cart{text-align:center;padding:60px 20px;color:var(--ink-soft)}
.empty-cart p{font-size:1.1rem;margin:16px 0}

@media(max-width:600px){
  .cart-head,.cart-row{grid-template-columns:1fr auto;gap:8px;padding:12px 14px}
  .cart-head .col-price,.cart-head .col-total{display:none}
  .cart-row .item-price,.cart-row .item-total{display:none}
  .item-info{align-items:flex-start}
  .item-name{font-size:.9rem;word-break:break-word}
  .inline-qty input{width:46px}
  .coupon-form{flex-direction:column}
  .coupon-form button{width:100%}
}
</style>
@endpush

@section('content')
<div class="wrap">
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

    {{-- Items --}}
    <div>
      <div class="cart-table">
        <div class="cart-head">
          <span>المنتج</span>
          <span class="col-price">السعر</span>
          <span class="col-total">الإجمالي</span>
          <span></span>
        </div>

        @foreach($cart as $key => $line)
        <div class="cart-row">
          {{-- Info --}}
          <div class="item-info">
            @if($line['image'])
              <img src="{{ $line['image'] }}" alt="{{ $line['name'] }}" class="item-img">
            @else
              <div class="item-img placeholder">🌿</div>
            @endif
            <div>
              <div class="item-name">{{ $line['name'] }}</div>
              <div class="item-sub">
                @php
                  $priceFmt = number_format($line['price_minor'] / 100, 2) . ' ج.م';
                @endphp
                {{ $priceFmt }} / {{ $line['is_weighted'] ? 'كجم' : $line['unit_label'] }}
              </div>
              {{-- Qty control --}}
              <form action="{{ route('storefront.cart.update') }}" method="POST" class="inline-qty" style="margin-top:8px">
                @csrf
                <input type="hidden" name="variant_id" value="{{ $line['variant_id'] }}">
                <button type="submit" name="qty" value="{{ max(0, $line['qty'] - $line['step']) }}">−</button>
                <input type="number" name="qty" value="{{ $line['qty'] }}" step="{{ $line['step'] }}"
                       onchange="this.form.submit()">
                <button type="submit" name="qty" value="{{ $line['qty'] + $line['step'] }}">+</button>
                <span style="font-size:.8rem;color:var(--ink-soft)">{{ $line['is_weighted'] ? 'جم' : $line['unit_label'] }}</span>
              </form>
            </div>
          </div>

          {{-- Unit price --}}
          <div class="item-price">{{ $priceFmt }}</div>

          {{-- Line total --}}
          <div class="item-price item-total">{{ number_format($line['line_total_minor'] / 100, 2) }} ج.م</div>

          {{-- Remove --}}
          <form action="{{ route('storefront.cart.remove', $line['variant_id']) }}" method="POST">
            @csrf @method('DELETE')
            <button type="submit" class="del-btn" title="حذف">×</button>
          </form>
        </div>
        @endforeach
      </div>

      <div style="margin-top:16px">
        <a href="{{ route('storefront.catalog') }}" class="btn-outline">
          ← مواصلة التسوق
        </a>
      </div>
    </div>

    {{-- Summary --}}
    <div class="summary-card">
      <h3>ملخص الطلب</h3>

      <div class="summary-row">
        <span>المجموع الفرعي</span>
        <span>{{ number_format($subtotal / 100, 2) }} ج.م</span>
      </div>

      @if($discountMinor > 0)
      <div class="summary-row">
        <span>خصم الكوبون ({{ $coupon }})</span>
        <span class="discount-val">− {{ number_format($discountMinor / 100, 2) }} ج.م</span>
      </div>
      @endif

      @php $taxMinor = (int) round($total - ($total / 1.14)); @endphp
      <div class="summary-row">
        <span>ض.ق.م 14% (شاملة)</span>
        <span>{{ number_format($taxMinor / 100, 2) }} ج.م</span>
      </div>

      <div class="summary-row">
        <span>الإجمالي المستحق</span>
        <strong>{{ number_format($total / 100, 2) }} ج.م</strong>
      </div>

      {{-- Coupon --}}
      @if($coupon)
      <div class="coupon-applied">
        <span>✓ كوبون «{{ $coupon }}» مفعَّل</span>
        <form action="{{ route('storefront.cart.coupon') }}" method="POST" style="display:inline">
          @csrf
          <button type="submit" name="coupon" value="" style="background:none;border:none;color:#065f46;cursor:pointer;font-weight:700;font-size:.85rem">إزالة</button>
        </form>
      </div>
      @else
      <form action="{{ route('storefront.cart.coupon') }}" method="POST" class="coupon-form">
        @csrf
        <input type="text" name="coupon" placeholder="كوبون الخصم…" style="text-transform:uppercase">
        <button type="submit">تطبيق</button>
      </form>
      @endif

      <a href="{{ route('storefront.checkout') }}" class="checkout-btn">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
        </svg>
        إتمام الطلب
      </a>
    </div>
  </div>
  @endif
</div>
@endsection
