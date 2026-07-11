@extends('layouts.storefront')

@section('title', 'إتمام الطلب — عبد القادر العطّار')

@push('head-styles')
<style>
.checkout-page{width:100%;max-width:100%;overflow-x:hidden}
.page-title{font-family:var(--font-thuluth);font-size:1.9rem;font-weight:400;padding:24px 0 6px}
.page-sub{color:var(--ink-soft);font-size:.9rem;margin-bottom:14px;line-height:1.65}
.delivery-pill{display:inline-flex;align-items:center;gap:6px;background:#d1fae5;color:#065f46;
  padding:6px 12px;border-radius:20px;font-size:.78rem;font-weight:600;margin-bottom:16px;line-height:1.5}
.money{unicode-bidi:isolate;direction:ltr;display:inline-block}

.checkout-grid{display:grid;grid-template-columns:minmax(0,1fr) minmax(0,300px);gap:22px;padding-bottom:50px;align-items:start}
.checkout-main,.checkout-side{min-width:0}

.form-box{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);padding:22px;margin-bottom:16px;overflow:hidden}
.form-box h2{font-family:var(--font-ui);font-size:1rem;font-weight:700;margin-bottom:14px}
.field{display:flex;flex-direction:column;gap:4px;margin-bottom:12px}
.field label{font-size:.84rem;font-weight:600;color:var(--ink-soft)}
.field label .req{color:var(--clay)}
.field input,.field textarea,.field select{
  width:100%;max-width:100%;padding:12px 14px;border:1.5px solid var(--hair);border-radius:10px;
  font-size:.95rem;background:var(--parchment-2);color:var(--ink);outline:none}
.field input:focus,.field textarea:focus,.field select:focus{border-color:var(--gold);background:var(--card)}
.field .err{font-size:.76rem;color:var(--clay)}

.pay-list{display:flex;flex-direction:column;gap:8px}
.pay-card{border:2px solid var(--hair);border-radius:12px;padding:14px;cursor:pointer;background:var(--parchment-2);overflow:hidden}
.pay-card.on{border-color:var(--gold-deep);background:var(--card)}
.pay-card input{display:none}
.pay-row{display:flex;align-items:flex-start;gap:10px}
.pay-ico{width:38px;height:38px;border-radius:9px;display:flex;align-items:center;justify-content:center;font-size:1rem;flex-shrink:0}
.pay-ico.cash{background:var(--ink);color:#fff}
.pay-ico.insta{background:#6c2bd9;color:#fff}
.pay-ico.voda{background:#e60000;color:#fff}
.pay-t{font-weight:700;font-size:.9rem;line-height:1.4}
.pay-s{font-size:.78rem;color:var(--ink-soft);margin-top:2px;line-height:1.45}
.pay-num{margin-top:8px;padding:9px;background:#fef3c7;border-radius:8px;font-weight:700;font-size:.92rem;
  direction:ltr;text-align:center;word-break:break-all;max-width:100%}

.sum-box{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);padding:20px;position:sticky;top:90px}
.sum-box h3{font-family:var(--font-ui);font-size:1rem;font-weight:700;margin-bottom:12px}
.sum-toggle{display:none;width:100%;background:var(--card);border:1px solid var(--hair);border-radius:14px;
  padding:14px;margin-bottom:12px;cursor:pointer;text-align:inherit;color:inherit;box-shadow:0 4px 16px -8px rgba(42,24,16,.1)}
.sum-toggle .top{display:flex;justify-content:space-between;align-items:center;gap:8px;width:100%}
.sum-toggle h3{font-family:var(--font-ui);font-size:.9rem;font-weight:700}
.sum-toggle .hint{font-size:.72rem;color:var(--ink-soft);margin-top:2px}
.sum-toggle .amt{color:var(--gold-deep);font-size:1rem;font-weight:800;white-space:nowrap}
.sum-toggle .details{margin-top:10px;padding-top:10px;border-top:1px dashed var(--hair)}
.sum-toggle .details[hidden]{display:none}

.sum-row{display:flex;justify-content:space-between;gap:8px;padding:7px 0;border-bottom:1px solid var(--hair);font-size:.86rem}
.sum-row span:first-child{flex:1;min-width:0;word-break:break-word;line-height:1.4}
.sum-row strong,.sum-row span:last-child{flex-shrink:0;white-space:nowrap}
.sum-grand{display:flex;justify-content:space-between;margin-top:10px;padding-top:10px;border-top:2px solid var(--hair);font-weight:800;font-size:1.1rem}
.sum-grand span:last-child{color:var(--gold-deep)}
.btn-place{width:100%;margin-top:14px;padding:15px;background:var(--ink);color:#fff;border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer}
.btn-place:hover{background:var(--gold-deep)}

.checkout-m-bar{
  display:none;position:fixed;inset-inline:0;bottom:0;z-index:55;width:100%;max-width:100vw;
  background:#fff;border-top:1px solid var(--hair);
  padding:10px 16px;padding-bottom:calc(10px + env(safe-area-inset-bottom,0px));
  box-shadow:0 -6px 24px -6px rgba(42,24,16,.12);
}
.checkout-m-bar .row{display:flex;align-items:center;gap:10px;width:100%}
.checkout-m-bar .lbl{flex:1;min-width:0}
.checkout-m-bar .lbl small{display:block;font-size:.68rem;color:var(--ink-soft)}
.checkout-m-bar .lbl strong{font-size:1.05rem;color:var(--gold-deep)}
.checkout-m-bar .btn-place{margin:0;flex:1;min-width:0;padding:13px 10px;font-size:.88rem;border-radius:11px}

@media(max-width:767px){
  .checkout-grid{display:flex;flex-direction:column;gap:0;padding-bottom:calc(88px + env(safe-area-inset-bottom,0px))}
  .checkout-side{order:-1}
  .sum-box{background:transparent;border:none;padding:0;position:static;box-shadow:none}
  .sum-box > h3,.sum-box .sum-row,.sum-box .sum-grand,.sum-box .btn-place,.sum-box > p{display:none}
  .sum-toggle{display:block}
  .checkout-m-bar{display:block}
  .page-title{font-size:1.3rem;padding:14px 0 4px}
  .page-sub{font-size:.82rem;margin-bottom:10px}
  .delivery-pill{font-size:.72rem;padding:5px 10px;margin-bottom:12px}
  .form-box{padding:16px;border-radius:14px;margin-bottom:10px}
  .form-box h2{font-size:.92rem}
  .field input,.field textarea,.field select{font-size:16px;min-height:48px;border-radius:12px}
  .pay-card{padding:12px}
}
</style>
@endpush

@section('content')
<div class="wrap checkout-page" x-data="checkoutForm(@js($payment), {{ (int) $total }})">
  <h1 class="page-title">إتمام الطلب</h1>
  <p class="page-sub">خطوة واحدة وتصلك مشترياتك</p>
  <div class="delivery-pill">🚚 التوصيل: المنصورة وطلخا</div>

  @if($errors->any())
  <div class="flash flash-error" style="margin-bottom:12px">
    @foreach($errors->all() as $err)<div>• {{ $err }}</div>@endforeach
  </div>
  @endif

  @php $totalFmt = number_format($total / 100, 2); @endphp

  <form id="checkout-form" action="{{ route('storefront.checkout.store') }}" method="POST">
    @csrf
    <div class="checkout-grid">
      <div class="checkout-main">
        <div class="form-box">
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
          <div class="field" style="margin-bottom:0">
            <label>ملاحظة</label>
            <input type="text" name="notes" value="{{ old('notes') }}" placeholder="اختياري">
          </div>
        </div>

        <div class="form-box">
          <h2>طريقة الدفع</h2>
          <div class="pay-list">
            <label class="pay-card" :class="method === 'cod' ? 'on' : ''" @click="method = 'cod'">
              <input type="radio" name="payment_method" value="cod" x-model="method">
              <div class="pay-row">
                <div class="pay-ico cash">💵</div>
                <div><div class="pay-t">كاش عند الاستلام</div><div class="pay-s">ادفع للمندوب عند الاستلام</div></div>
              </div>
            </label>
            <label class="pay-card" :class="method === 'instapay' ? 'on' : ''" @click="method = 'instapay'" x-show="numbers.instapay">
              <input type="radio" name="payment_method" value="instapay" x-model="method">
              <div class="pay-row">
                <div class="pay-ico insta">📲</div>
                <div><div class="pay-t">إنستاباي</div><div class="pay-s">حوّل وابعتلنا إيصال</div></div>
              </div>
              <div class="pay-num" x-show="method === 'instapay'" x-cloak x-text="numbers.instapay"></div>
            </label>
            <label class="pay-card" :class="method === 'vodafone_cash' ? 'on' : ''" @click="method = 'vodafone_cash'" x-show="numbers.vodafone_cash">
              <input type="radio" name="payment_method" value="vodafone_cash" x-model="method">
              <div class="pay-row">
                <div class="pay-ico voda">📱</div>
                <div><div class="pay-t">فودافون كاش</div><div class="pay-s">حوّل على المحفظة</div></div>
              </div>
              <div class="pay-num" x-show="method === 'vodafone_cash'" x-cloak x-text="numbers.vodafone_cash"></div>
            </label>
          </div>
          @error('payment_method')<span class="err">{{ $message }}</span>@enderror
        </div>
      </div>

      <div class="checkout-side">
        <div class="sum-box">
          <button type="button" class="sum-toggle" @click="summaryOpen = !summaryOpen">
            <div class="top">
              <div>
                <h3>ملخص ({{ count($cart) }} صنف)</h3>
                <div class="hint" x-text="summaryOpen ? 'إخفاء ▴' : 'عرض التفاصيل ▾'"></div>
              </div>
              <span class="amt"><span class="money">{{ $totalFmt }}</span> ج.م</span>
            </div>
            <div class="details" :hidden="!summaryOpen" @click.stop>
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

          <h3>ملخص ({{ count($cart) }} صنف)</h3>
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
          <button type="submit" class="btn-place">تأكيد الطلب ✓</button>
          <p style="text-align:center;font-size:.78rem;color:var(--ink-soft);margin-top:10px">
            <a href="{{ route('storefront.track.lookup') }}" style="color:var(--gold-deep);font-weight:600">تتبّع طلبك لاحقًا</a>
          </p>
        </div>
      </div>
    </div>
  </form>

  <div class="checkout-m-bar">
    <div class="row">
      <div class="lbl">
        <small>الإجمالي</small>
        <strong x-text="totalFmt"></strong>
      </div>
      <button type="submit" form="checkout-form" class="btn-place">تأكيد الطلب ✓</button>
    </div>
  </div>
</div>
@endsection

@push('scripts')
<script>
function checkoutForm(numbers, totalMinor) {
    return {
        method: '{{ old('payment_method', 'cod') }}',
        numbers,
        summaryOpen: false,
        totalFmt: (totalMinor / 100).toLocaleString('ar-EG', { minimumFractionDigits: 2, maximumFractionDigits: 2 }) + ' ج.م',
    };
}
</script>
@endpush
