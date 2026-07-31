@extends('layouts.storefront')

@section('title', 'إتمام الطلب — ' . $shop['name'])

@push('head-styles')
<style>
.app-check{max-width:560px;margin:0 auto;padding-bottom:calc(100px + env(safe-area-inset-bottom,0px))}
.app-check-head{display:flex;align-items:center;gap:12px;padding:16px 4px 8px}
.app-check-head a.back{
  width:42px;height:42px;border-radius:14px;display:grid;place-items:center;
  background:var(--card);border:1px solid var(--hair);color:var(--ink);flex-shrink:0;
}
.app-check-head h1{font-family:var(--font-thuluth);font-size:1.55rem;font-weight:400;flex:1}
.app-check-sub{color:var(--ink-soft);font-size:.88rem;padding:0 4px 14px;line-height:1.6}
.delivery-pill{
  display:inline-flex;align-items:center;gap:6px;margin:0 4px 14px;
  background:rgba(47,74,56,.1);color:var(--emerald);padding:8px 14px;border-radius:12px;
  font-size:.78rem;font-weight:600;font-family:var(--font-ui);
}
.money{unicode-bidi:isolate;direction:ltr;display:inline-block}

.app-card{
  background:var(--card);border:1px solid var(--hair);border-radius:20px;padding:18px;
  margin-bottom:12px;box-shadow:0 8px 28px -18px rgba(12,10,8,.18);
}
.app-card h2{font-family:var(--font-ui);font-size:.92rem;font-weight:700;margin-bottom:14px}
.field{display:flex;flex-direction:column;gap:6px;margin-bottom:14px}
.field:last-child{margin-bottom:0}
.field label{font-size:.82rem;font-weight:600;color:var(--ink-soft)}
.field label .req{color:var(--clay)}
.field input,.field textarea,.field select{
  width:100%;padding:14px 16px;border:1.5px solid var(--hair);border-radius:14px;
  font-size:16px;background:var(--parchment-2);color:var(--ink);outline:none;min-height:52px;
}
.field textarea{min-height:88px;resize:vertical}
.field input:focus,.field textarea:focus,.field select:focus{border-color:var(--gold);background:var(--card)}
.field .err{font-size:.76rem;color:var(--clay)}

.pay-list{display:flex;flex-direction:column;gap:10px}
.pay-card{
  border:2px solid var(--hair);border-radius:16px;padding:14px;cursor:pointer;
  background:var(--parchment-2);transition:border-color .15s,background .15s;
}
.pay-card.on{border-color:var(--gold);background:var(--card);box-shadow:0 0 0 3px rgba(201,146,46,.15)}
.pay-card input{display:none}
.pay-row{display:flex;align-items:flex-start;gap:12px}
.pay-ico{
  width:44px;height:44px;border-radius:12px;display:grid;place-items:center;
  font-size:1.1rem;flex-shrink:0;font-weight:800;color:#fff;
}
.pay-ico.cash{background:var(--ink)}
.pay-ico.insta{background:#5b21b6}
.pay-ico.voda{background:#c40000}
.pay-t{font-weight:700;font-size:.95rem}
.pay-s{font-size:.78rem;color:var(--ink-soft);margin-top:3px;line-height:1.45}
.pay-num{
  margin-top:10px;padding:12px;background:rgba(201,146,46,.12);border-radius:12px;
  font-weight:700;font-size:.95rem;direction:ltr;text-align:center;word-break:break-all;
}

.sum-toggle{
  width:100%;background:var(--card);border:1px solid var(--hair);border-radius:18px;
  padding:14px 16px;margin-bottom:12px;cursor:pointer;text-align:inherit;color:inherit;
  box-shadow:0 8px 28px -18px rgba(12,10,8,.15);
}
.sum-toggle .top{display:flex;justify-content:space-between;align-items:center;gap:8px}
.sum-toggle h3{font-family:var(--font-ui);font-size:.88rem;font-weight:700}
.sum-toggle .hint{font-size:.72rem;color:var(--ink-soft);margin-top:2px}
.sum-toggle .amt{color:var(--gold-deep);font-size:1.05rem;font-weight:800}
.sum-toggle .details{margin-top:12px;padding-top:12px;border-top:1px dashed var(--hair)}
.sum-row{display:flex;justify-content:space-between;gap:8px;padding:7px 0;font-size:.86rem}
.sum-row span:first-child{flex:1;min-width:0;word-break:break-word;line-height:1.4;color:var(--ink-soft)}
.sum-grand{display:flex;justify-content:space-between;margin-top:8px;padding-top:10px;border-top:2px solid var(--hair);font-weight:800;font-size:1.05rem}

.app-dock{
  position:fixed;inset-inline:0;bottom:0;z-index:70;
  background:rgba(255,252,248,.94);backdrop-filter:blur(16px);
  border-top:1px solid var(--hair);
  padding:12px 16px;padding-bottom:calc(12px + env(safe-area-inset-bottom,0px));
  box-shadow:0 -12px 40px -16px rgba(12,10,8,.2);
}
.app-dock-inner{max-width:560px;margin:0 auto;display:flex;align-items:center;gap:12px}
.app-dock .tot small{display:block;font-size:.7rem;color:var(--ink-soft);font-family:var(--font-ui)}
.app-dock .tot strong{font-size:1.15rem;color:var(--gold-deep)}
.app-dock .cta{
  flex:1;min-height:52px;border:none;border-radius:16px;cursor:pointer;
  background:linear-gradient(135deg,#1a1510,#0c0a08);color:var(--gold-light);
  font-weight:800;font-size:1rem;
  box-shadow:0 12px 28px -10px rgba(12,10,8,.45);
}
.app-dock .cta:active{transform:scale(.98)}

@media(min-width:900px){
  .app-check{max-width:640px}
}
</style>
@endpush

@section('content')
@php $totalFmt = number_format($total / 100, 2); @endphp

<div class="wrap app-check" x-data="checkoutForm(@js($payment), {{ (int) $total }})">
  <div class="app-check-head">
    <a href="{{ route('storefront.cart') }}" class="back" aria-label="رجوع للسلة">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>
    <h1>إتمام الطلب</h1>
  </div>
  <p class="app-check-sub">خطوة واحدة وتصلك مشترياتك لباب البيت</p>
  <div class="delivery-pill">توصيل {{ $shop['governorate'] }} · {{ implode(' و', array_slice($shop['delivery_cities'], 0, 2)) }}</div>

  @if($errors->any())
  <div class="flash flash-error" style="margin-bottom:12px">
    @foreach($errors->all() as $err)<div>• {{ $err }}</div>@endforeach
  </div>
  @endif

  <form id="checkout-form" action="{{ route('storefront.checkout.store') }}" method="POST">
    @csrf

    <button type="button" class="sum-toggle" @click="summaryOpen = !summaryOpen">
      <div class="top">
        <div>
          <h3>ملخص ({{ count($cart) }} أصناف)</h3>
          <div class="hint" x-text="summaryOpen ? 'إخفاء التفاصيل ▴' : 'عرض التفاصيل ▾'"></div>
        </div>
        <span class="amt"><span class="money">{{ $totalFmt }}</span> ج.م</span>
      </div>
      <div class="details" x-show="summaryOpen" x-cloak @click.stop>
        @foreach($cart as $line)
        <div class="sum-row">
          <span>{{ $line['name'] }} × {{ $line['is_weighted'] ? $line['qty'].' جم' : $line['qty'] }}</span>
          <strong><span class="money">{{ number_format($line['line_total_minor'] / 100, 2) }}</span> ج.م</strong>
        </div>
        @endforeach
        @if($discountMinor > 0)
        <div class="sum-row" style="color:var(--olive)"><span>خصم</span><span>− <span class="money">{{ number_format($discountMinor / 100, 2) }}</span> ج.م</span></div>
        @endif
        <div class="sum-grand"><span>الإجمالي</span><span><span class="money">{{ $totalFmt }}</span> ج.م</span></div>
      </div>
    </button>

    <div class="app-card">
      <h2>بيانات التوصيل</h2>
      <div class="field">
        <label>الاسم <span class="req">*</span></label>
        <input type="text" name="name" value="{{ old('name') }}" required placeholder="اسمك الكامل" autocomplete="name">
        @error('name')<span class="err">{{ $message }}</span>@enderror
      </div>
      <div class="field">
        <label>رقم الموبايل <span class="req">*</span></label>
        <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="01xxxxxxxxx" dir="ltr" inputmode="tel" autocomplete="tel">
        @error('phone')<span class="err">{{ $message }}</span>@enderror
      </div>
      <div class="field">
        <label>المنطقة <span class="req">*</span></label>
        <select name="city" required>
          <option value="">اختر المنطقة</option>
          @foreach(\App\Support\StorefrontCheckout::cities() as $city)
            <option value="{{ $city }}" {{ old('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
          @endforeach
        </select>
        @error('city')<span class="err">{{ $message }}</span>@enderror
      </div>
      <div class="field">
        <label>العنوان <span class="req">*</span></label>
        <textarea name="address" rows="2" required placeholder="الشارع — المبنى — علامة مميزة">{{ old('address') }}</textarea>
        @error('address')<span class="err">{{ $message }}</span>@enderror
      </div>
      <div class="field">
        <label>ملاحظة للمندوب</label>
        <input type="text" name="notes" value="{{ old('notes') }}" placeholder="اختياري">
      </div>
    </div>

    <div class="app-card">
      <h2>طريقة الدفع</h2>
      <div class="pay-list">
        <label class="pay-card" :class="method === 'cod' && 'on'">
          <input type="radio" name="payment_method" value="cod" x-model="method">
          <div class="pay-row">
            <div class="pay-ico cash">ك</div>
            <div><div class="pay-t">كاش عند الاستلام</div><div class="pay-s">ادفع للمندوب عند باب البيت</div></div>
          </div>
        </label>
        <label class="pay-card" :class="method === 'instapay' && 'on'" x-show="numbers.instapay">
          <input type="radio" name="payment_method" value="instapay" x-model="method">
          <div class="pay-row">
            <div class="pay-ico insta">إ</div>
            <div><div class="pay-t">إنستاباي</div><div class="pay-s">حوّل وابعتلنا إيصال على واتساب</div></div>
          </div>
          <div class="pay-num" x-show="method === 'instapay'" x-cloak x-text="numbers.instapay"></div>
        </label>
        <label class="pay-card" :class="method === 'vodafone_cash' && 'on'" x-show="numbers.vodafone_cash">
          <input type="radio" name="payment_method" value="vodafone_cash" x-model="method">
          <div class="pay-row">
            <div class="pay-ico voda">ف</div>
            <div><div class="pay-t">فودافون كاش</div><div class="pay-s">حوّل على المحفظة</div></div>
          </div>
          <div class="pay-num" x-show="method === 'vodafone_cash'" x-cloak x-text="numbers.vodafone_cash"></div>
        </label>
      </div>
      @error('payment_method')<span class="err">{{ $message }}</span>@enderror
    </div>
  </form>

  <div class="app-dock">
    <div class="app-dock-inner">
      <div class="tot">
        <small>الإجمالي</small>
        <strong x-text="totalFmt"></strong>
      </div>
      <button type="submit" form="checkout-form" class="cta">تأكيد الطلب</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function checkoutForm(numbers, totalMinor) {
    return {
        method: @js(old('payment_method', 'cod')),
        numbers,
        summaryOpen: false,
        totalFmt: (totalMinor / 100).toLocaleString('ar-EG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ج.م',
    };
}
</script>
@endpush
