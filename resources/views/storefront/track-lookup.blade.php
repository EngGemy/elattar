@extends('layouts.storefront')

@section('title', 'تتبّع طلبك — عبد القادر العطّار')

@push('head-styles')
<style>
.track-hero{text-align:center;padding:40px 0 24px}
.track-hero h1{font-family:var(--font-thuluth);font-size:clamp(1.75rem,4vw,2.4rem);margin-bottom:8px;font-weight:400}
.track-hero p{color:var(--ink-soft);font-size:.95rem;max-width:420px;margin:0 auto;line-height:1.6}

.track-card{max-width:440px;margin:0 auto 60px;background:var(--card);border:1px solid var(--hair);
  border-radius:var(--radius);padding:28px 24px;box-shadow:0 16px 40px -20px rgba(36,26,17,.4)}
@media(max-width:600px){
  .track-hero{padding:24px 0 16px}
  .track-card{margin:0 0 40px;padding:20px 16px;border-radius:14px}
  .track-card h2{font-size:.98rem}
  .field input{padding:12px 13px;font-size:.95rem}
}
.track-card h2{font-family:'Reem Kufi';font-size:1.05rem;font-weight:700;margin-bottom:18px;text-align:center}
.field{margin-bottom:16px}
.field label{display:block;font-size:.86rem;font-weight:600;color:var(--ink-soft);margin-bottom:6px}
.field input{width:100%;padding:13px 15px;border:1.5px solid var(--hair);border-radius:11px;
  font-size:1rem;background:var(--parchment-2);outline:none}
.field input:focus{border-color:var(--gold);background:var(--card)}
.field input.order-num{font-family:'Reem Kufi';letter-spacing:.5px;text-transform:uppercase}
.field input.phone{direction:ltr;text-align:left}
.field-hint{font-size:.76rem;color:var(--ink-soft);margin-top:5px}
.track-btn{width:100%;padding:14px;background:var(--ink);color:var(--parchment);border:none;
  border-radius:12px;font-size:1rem;font-weight:700;cursor:pointer;margin-top:6px}
.track-btn:hover{background:var(--gold-deep)}
.track-help{text-align:center;margin-top:18px;font-size:.84rem;color:var(--ink-soft)}
.track-help a{color:var(--gold-deep);font-weight:600}
</style>
@endpush

@section('content')
<div class="wrap">
  <div class="track-hero">
    <h1>تتبّع طلبك</h1>
    <p>أدخل رقم الطلب ورقم الموبايل اللي طلبت بيه — هتشوف حالة طلبك فورًا</p>
  </div>

  <div class="track-card">
    <h2>🔍 ابحث عن طلبك</h2>

    <form action="{{ route('storefront.track.search') }}" method="POST">
      @csrf
      <div class="field">
        <label for="number">رقم الطلب</label>
        <input type="text" id="number" name="number" class="order-num"
               value="{{ old('number') }}" required
               placeholder="ORD-2026-000001" autocomplete="off">
        <p class="field-hint">الرقم اللي وصلك بعد تأكيد الطلب</p>
        @error('number')<p style="color:var(--clay);font-size:.8rem;margin-top:4px">{{ $message }}</p>@enderror
      </div>

      <div class="field">
        <label for="phone">رقم الموبايل</label>
        <input type="tel" id="phone" name="phone" class="phone"
               value="{{ old('phone') }}" required
               placeholder="01xxxxxxxxx">
        <p class="field-hint">نفس الرقم اللي كتبته وقت الطلب</p>
        @error('phone')<p style="color:var(--clay);font-size:.8rem;margin-top:4px">{{ $message }}</p>@enderror
      </div>

      <button type="submit" class="track-btn">عرض حالة الطلب</button>
    </form>

    <p class="track-help">
      محتاج مساعدة؟
      <a href="https://wa.me/{{ \App\Support\StorefrontWhatsApp::number() }}" target="_blank" rel="noopener">تواصل على واتساب</a>
    </p>
  </div>
</div>
@endsection
