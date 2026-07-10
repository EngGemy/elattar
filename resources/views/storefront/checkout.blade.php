@extends('layouts.storefront')

@section('title', 'إتمام الطلب — عبد القادر العطّار')

@push('head-styles')
<style>
.page-title{font-family:'Amiri';font-size:1.9rem;font-weight:700;padding:24px 0 8px}
.page-sub{color:var(--ink-soft);font-size:.92rem;margin-bottom:20px}
.delivery-pill{display:inline-flex;align-items:center;gap:6px;background:#d1fae5;color:#065f46;
  padding:6px 14px;border-radius:20px;font-size:.82rem;font-weight:600;margin-bottom:20px}

.checkout-layout{display:grid;grid-template-columns:1fr 320px;gap:22px;padding-bottom:50px;align-items:start}
@media(max-width:780px){.checkout-layout{grid-template-columns:1fr}}

.form-card{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);padding:22px;margin-bottom:16px}
.form-card h2{font-family:'Reem Kufi';font-size:1rem;font-weight:700;margin-bottom:16px;color:var(--ink)}
.field{display:flex;flex-direction:column;gap:5px;margin-bottom:14px}
.field label{font-size:.86rem;font-weight:600;color:var(--ink-soft)}
.field label span.req{color:var(--clay)}
.field input,.field textarea,.field select{
  padding:12px 14px;border:1.5px solid var(--hair);border-radius:10px;
  font-size:.95rem;background:var(--parchment-2);color:var(--ink);outline:none;width:100%}
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
.sum-item{display:flex;justify-content:space-between;gap:8px;padding:8px 0;border-bottom:1px solid var(--hair);font-size:.86rem}
.sum-item:last-child{border-bottom:none}
.sum-total{display:flex;justify-content:space-between;align-items:center;margin-top:12px;padding-top:12px;border-top:2px solid var(--hair)}
.sum-total span:last-child{font-family:'Reem Kufi';font-weight:800;font-size:1.2rem;color:var(--gold-deep)}
.place-btn{width:100%;margin-top:16px;padding:15px;background:var(--ink);color:var(--parchment);
  border:none;border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer}
.place-btn:hover{background:var(--gold-deep)}
</style>
@endpush

@section('content')
<div class="wrap" x-data="checkoutForm(@js($payment))">
  <h1 class="page-title">إتمام الطلب</h1>
  <p class="page-sub">خطوة واحدة وتصلك مشترياتك</p>
  <div class="delivery-pill">🚚 التوصيل: الدقهلية — المنصورة وطلخا فقط</div>

  @if($errors->any())
  <div class="flash flash-error" style="margin-bottom:16px">
    @foreach($errors->all() as $err)<div>• {{ $err }}</div>@endforeach
  </div>
  @endif

  <form action="{{ route('storefront.checkout.store') }}" method="POST">
    @csrf
  <div class="checkout-layout">

    <div>
      <div class="form-card">
        <h2>بياناتك</h2>
        <div class="field">
          <label>الاسم <span class="req">*</span></label>
          <input type="text" name="name" value="{{ old('name') }}" required placeholder="اسمك الكامل">
          @error('name')<span class="err">{{ $message }}</span>@enderror
        </div>
        <div class="field">
          <label>رقم الموبايل <span class="req">*</span></label>
          <input type="tel" name="phone" value="{{ old('phone') }}" required placeholder="01xxxxxxxxx" dir="ltr">
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
</div>
@endsection

@push('scripts')
<script>
function checkoutForm(numbers) {
    return {
        method: '{{ old('payment_method', 'cod') }}',
        numbers,
    };
}
</script>
@endpush
