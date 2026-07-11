@extends('layouts.storefront')

@section('title', 'إتمام الطلب — عبد القادر العطّار')

@push('head-styles')
<style>
.page-title{font-family:var(--font-thuluth);font-size:1.9rem;font-weight:400;padding:24px 0 8px}
.page-sub{color:var(--ink-soft);font-size:.92rem;margin-bottom:20px}
.delivery-pill{display:inline-flex;align-items:center;gap:6px;background:#d1fae5;color:#065f46;
  padding:6px 14px;border-radius:20px;font-size:.82rem;font-weight:600;margin-bottom:20px}

.checkout-layout{display:grid;grid-template-columns:1fr 320px;gap:22px;padding-bottom:50px;align-items:start}
.checkout-form-col{min-width:0}

.form-card{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);padding:22px;margin-bottom:16px}
.form-card h2{font-family:'Reem Kufi';font-size:1rem;font-weight:700;margin-bottom:16px;color:var(--ink)}
.field{display:flex;flex-direction:column;gap:5px;margin-bottom:14px}
.field label{font-size:.86rem;font-weight:600;color:var(--ink-soft)}
.field label span.req{color:var(--clay)}
.field input,.field textarea,.field select{
  padding:12px 14px;border:1.5px solid var(--hair);border-radius:10px;
  font-size:.95rem;background:var(--parchment-2);color:var(--ink);outline:none;width:100%;
  -webkit-appearance:none;appearance:none}
.field input:focus,.field textarea:focus,.field select:focus{border-color:var(--gold);background:var(--card)}
.field .err{font-size:.78rem;color:var(--clay)}
.field-hint{font-size:.78rem;color:var(--ink-soft);margin-top:2px}

.pay-grid{display:flex;flex-direction:column;gap:10px}
.pay-opt{border:2px solid var(--hair);border-radius:12px;padding:14px;cursor:pointer;transition:.15s;background:var(--parchment-2)}
.pay-opt.sel{border-color:var(--gold-deep);background:var(--card)}
.pay-opt input{display:none}
.pay-top{display:flex;align-items:center;gap:12px}
.pay-icon{width:40px;height:40px;border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:1.1rem;flex-shrink:0}
.pay-icon.cash{background:var(--ink);color:var(--parchment)}
.pay-icon.insta{background:#6c2bd9;color:#fff}
.pay-icon.voda{background:#e60000;color:#fff}
.pay-label{font-weight:700;font-size:.94rem}
.pay-sub{font-size:.8rem;color:var(--ink-soft);margin-top:2px}
.pay-number{margin-top:10px;padding:10px 12px;background:#fef3c7;border-radius:8px;
  font-family:'Reem Kufi';font-weight:700;font-size:1rem;direction:ltr;text-align:center;color:var(--ink)}
.pay-number small{display:block;font-size:.72rem;font-weight:500;color:var(--ink-soft);margin-bottom:4px;direction:rtl}

.summary-card{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);padding:20px;position:sticky;top:90px}
.summary-card h3{font-family:'Reem Kufi';font-size:1rem;font-weight:700;margin-bottom:14px}
.sum-item{display:flex;justify-content:space-between;gap:8px;padding:8px 0;border-bottom:1px solid var(--hair);font-size:.86rem;align-items:flex-start}
.sum-item span:first-child{flex:1;min-width:0;word-break:break-word;line-height:1.45}
.sum-item:last-child{border-bottom:none}
.sum-total{display:flex;justify-content:space-between;align-items:center;margin-top:12px;padding-top:12px;border-top:2px solid var(--hair)}
.sum-total span:last-child{font-family:'Reem Kufi';font-weight:800;font-size:1.2rem;color:var(--gold-deep)}
.place-btn{width:100%;margin-top:16px;padding:15px;background:var(--ink);color:var(--parchment);
  border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer}
.place-btn:hover{background:var(--gold-deep)}

/* Mobile summary toggle */
.summary-toggle{display:none;width:100%;background:var(--card);border:1px solid var(--hair);border-radius:14px;
  padding:14px 16px;margin-bottom:12px;cursor:pointer;text-align:right}
.summary-toggle .row{display:flex;justify-content:space-between;align-items:center;gap:10px}
.summary-toggle .lbl{font-family:'Reem Kufi';font-weight:700;font-size:.95rem}
.summary-toggle .amt{font-family:'Reem Kufi';font-weight:800;color:var(--gold-deep);font-size:1.05rem}
.summary-toggle .hint{font-size:.78rem;color:var(--ink-soft);margin-top:4px}
.summary-details{display:none;margin-top:12px;padding-top:12px;border-top:1px dashed var(--hair)}
.summary-details.open{display:block}

.checkout-mobile-bar{
  display:none;position:fixed;left:0;right:0;bottom:0;z-index:55;
  background:rgba(255,255,255,.96);backdrop-filter:blur(12px);
  border-top:1px solid var(--hair);padding:12px clamp(16px,4vw,24px);
  padding-bottom:calc(12px + env(safe-area-inset-bottom, 0px));
  box-shadow:0 -8px 28px -8px rgba(42,24,16,.12);
}
.checkout-mobile-bar .inner{display:flex;align-items:center;gap:12px}
.checkout-mobile-bar .total{flex:1;min-width:0}
.checkout-mobile-bar .total small{display:block;font-size:.72rem;color:var(--ink-soft);margin-bottom:2px}
.checkout-mobile-bar .total strong{font-family:'Reem Kufi';font-size:1.15rem;color:var(--gold-deep)}
.checkout-mobile-bar .place-btn{margin:0;width:auto;flex:1;min-width:140px;padding:14px 16px;font-size:.95rem}

@media(max-width:780px){
  .checkout-layout{display:flex;flex-direction:column;gap:12px;padding-bottom:calc(100px + env(safe-area-inset-bottom, 0px))}
  .checkout-form-col{order:2}
  .summary-card{order:1;position:static;top:auto;padding:0;border:none;background:transparent;box-shadow:none}
  .summary-card > h3,.summary-card .place-btn,.summary-card > p{display:none}
  .summary-toggle{display:block}
  .checkout-mobile-bar{display:block}
  .page-title{font-size:1.45rem;padding:16px 0 4px;line-height:1.3}
  .page-sub{font-size:.84rem;margin-bottom:12px;line-height:1.6}
  .delivery-pill{font-size:.74rem;padding:5px 11px;margin-bottom:12px;max-width:100%;flex-wrap:wrap}
  .form-card{padding:16px;margin-bottom:12px;border-radius:14px}
  .form-card h2{font-size:.95rem;margin-bottom:14px}
  .field{margin-bottom:12px}
  .field input,.field textarea,.field select{
    font-size:16px;padding:13px 14px;border-radius:12px;min-height:48px}
  .field textarea{min-height:88px}
  .pay-opt{padding:14px 12px;border-radius:14px;min-height:56px}
  .pay-top{gap:10px;align-items:flex-start}
  .pay-icon{width:38px;height:38px;font-size:1rem;border-radius:9px}
  .pay-label{font-size:.9rem;line-height:1.35}
  .pay-sub{font-size:.76rem;line-height:1.45}
  .pay-number{font-size:.95rem;padding:10px}
  .sum-item{font-size:.84rem;padding:7px 0}
  .sum-total span:last-child{font-size:1.1rem}
}
</style>
@endpush

@section('content')
<div class="wrap" x-data="checkoutForm(@js($payment), {{ (int) $total }})">
  <h1 class="page-title">إتمام الطلب</h1>
  <p class="page-sub">خطوة واحدة وتصلك مشترياتك</p>
  <div class="delivery-pill">🚚 التوصيل: الدقهلية — المنصورة وطلخا فقط</div>

  @if($errors->any())
  <div class="flash flash-error" style="margin-bottom:16px">
    @foreach($errors->all() as $err)<div>• {{ $err }}</div>@endforeach
  </div>
  @endif

  <form id="checkout-form" action="{{ route('storefront.checkout.store') }}" method="POST">
    @csrf
  <div class="checkout-layout">

    <div class="checkout-form-col">
      <div class="form-card">
        <h2>بياناتك</h2>
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
          <label>العنوان بالتفصيل <span class="req">*</span></label>
          <textarea name="address" rows="2" required placeholder="الشارع — المبنى — علامة مميزة">{{ old('address') }}</textarea>
          @error('address')<span class="err">{{ $message }}</span>@enderror
        </div>
        <div class="field" style="margin-bottom:0">
          <label>ملاحظة (اختياري)</label>
          <input type="text" name="notes" value="{{ old('notes') }}" placeholder="مثال: اتصل قبل الوصول">
        </div>
      </div>

      <div class="form-card">
        <h2>طريقة الدفع</h2>
        <div class="pay-grid">
          <label class="pay-opt" :class="method === 'cod' ? 'sel' : ''" @click="method = 'cod'">
            <input type="radio" name="payment_method" value="cod" x-model="method">
            <div class="pay-top">
              <div class="pay-icon cash">💵</div>
              <div>
                <div class="pay-label">كاش عند الاستلام</div>
                <div class="pay-sub">ادفع للمندوب لما يوصلك الطلب</div>
              </div>
            </div>
          </label>

          <label class="pay-opt" :class="method === 'instapay' ? 'sel' : ''" @click="method = 'instapay'" x-show="numbers.instapay">
            <input type="radio" name="payment_method" value="instapay" x-model="method">
            <div class="pay-top">
              <div class="pay-icon insta">📲</div>
              <div>
                <div class="pay-label">إنستاباي</div>
                <div class="pay-sub">حوّل المبلغ وابعتلنا إيصال على واتساب</div>
              </div>
            </div>
            <div class="pay-number" x-show="method === 'instapay'" x-cloak>
              <small>رقم التحويل</small>
              <span x-text="numbers.instapay"></span>
            </div>
          </label>

          <label class="pay-opt" :class="method === 'vodafone_cash' ? 'sel' : ''" @click="method = 'vodafone_cash'" x-show="numbers.vodafone_cash">
            <input type="radio" name="payment_method" value="vodafone_cash" x-model="method">
            <div class="pay-top">
              <div class="pay-icon voda">📱</div>
              <div>
                <div class="pay-label">فودافون كاش</div>
                <div class="pay-sub">حوّل المبلغ على المحفظة</div>
              </div>
            </div>
            <div class="pay-number" x-show="method === 'vodafone_cash'" x-cloak>
              <small>رقم المحفظة</small>
              <span x-text="numbers.vodafone_cash"></span>
            </div>
          </label>
        </div>
        @error('payment_method')<span class="err">{{ $message }}</span>@enderror
      </div>
    </div>

    <div class="summary-card">
      <button type="button" class="summary-toggle" @click="summaryOpen = !summaryOpen">
        <div class="row">
          <div>
            <div class="lbl">ملخص الطلب ({{ count($cart) }} صنف)</div>
            <div class="hint" x-text="summaryOpen ? 'اضغط للإخفاء ▴' : 'اضغط لعرض التفاصيل ▾'"></div>
          </div>
          <div class="amt">{{ number_format($total / 100, 2) }} ج.م</div>
        </div>
        <div class="summary-details" :class="summaryOpen ? 'open' : ''" @click.stop>
          @foreach($cart as $line)
          <div class="sum-item">
            <span>{{ $line['name'] }} × {{ $line['is_weighted'] ? $line['qty'].'جم' : $line['qty'] }}</span>
            <strong>{{ number_format($line['line_total_minor'] / 100, 2) }} ج</strong>
          </div>
          @endforeach
          @if($discountMinor > 0)
          <div class="sum-item" style="color:var(--olive)">
            <span>خصم</span><span>− {{ number_format($discountMinor / 100, 2) }} ج</span>
          </div>
          @endif
          <div class="sum-total">
            <span>الإجمالي</span>
            <span>{{ number_format($total / 100, 2) }} ج.م</span>
          </div>
        </div>
      </button>

      <h3>ملخص ({{ count($cart) }} صنف)</h3>
      @foreach($cart as $line)
      <div class="sum-item">
        <span>{{ $line['name'] }} × {{ $line['is_weighted'] ? $line['qty'].'جم' : $line['qty'] }}</span>
        <strong>{{ number_format($line['line_total_minor'] / 100, 2) }} ج</strong>
      </div>
      @endforeach
      @if($discountMinor > 0)
      <div class="sum-item" style="color:var(--olive)">
        <span>خصم</span><span>− {{ number_format($discountMinor / 100, 2) }} ج</span>
      </div>
      @endif
      <div class="sum-total">
        <span>الإجمالي</span>
        <span>{{ number_format($total / 100, 2) }} ج.م</span>
      </div>
      <button type="submit" class="place-btn">تأكيد الطلب ✓</button>
      <p style="text-align:center;font-size:.8rem;color:var(--ink-soft);margin-top:12px">
        <a href="{{ route('storefront.track.lookup') }}" style="color:var(--gold-deep);font-weight:600">تتبّع طلبك لاحقًا</a>
      </p>
    </div>

  </div>
  </form>

  <div class="checkout-mobile-bar">
    <div class="inner">
      <div class="total">
        <small>الإجمالي</small>
        <strong x-text="totalFmt"></strong>
      </div>
      <button type="submit" form="checkout-form" class="place-btn">تأكيد الطلب ✓</button>
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
