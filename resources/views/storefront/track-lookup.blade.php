@extends('layouts.storefront')

@section('title', 'تتبّع طلبك — ' . $shop['name'])

@push('head-styles')
<style>
.trk{max-width:440px;margin:0 auto;padding:20px 16px 40px}
.trk-head{margin-bottom:18px}
.trk-head a.back{
  width:42px;height:42px;border-radius:14px;display:grid;place-items:center;
  background:var(--card);border:1px solid var(--hair);margin-bottom:14px;color:var(--ink);
}
.trk-head h1{font-family:var(--font-thuluth);font-size:1.6rem;font-weight:700}
.trk-head p{color:var(--ink-soft);font-size:.9rem;margin-top:6px;line-height:1.7}
.trk-card{
  background:var(--card);border:1px solid var(--hair);border-radius:22px;padding:22px 18px;
  box-shadow:0 16px 40px -22px rgba(11,22,18,.3);
  animation:rise .5s cubic-bezier(.2,.8,.2,1) both;
}
@keyframes rise{from{opacity:0;transform:translateY(16px)}to{opacity:1;transform:none}}
.trk-card h2{font-family:var(--font-ui);font-size:.95rem;font-weight:700;margin-bottom:16px}
.field{margin-bottom:14px}
.field label{display:block;font-size:.8rem;font-weight:600;color:var(--ink-soft);margin-bottom:6px;font-family:var(--font-ui)}
.field input{
  width:100%;height:50px;padding:0 14px;border:1.5px solid var(--hair);border-radius:14px;
  font-size:16px;background:var(--parchment-2);outline:none;font-family:var(--font-ui);
}
.field input:focus{border-color:var(--gold);background:var(--card)}
.field input.phone{direction:ltr;text-align:left}
.field-hint{font-size:.74rem;color:var(--ink-soft);margin-top:5px}
.trk-btn{
  width:100%;height:52px;border:none;border-radius:14px;margin-top:6px;
  background:var(--emerald);color:#fff;font-size:1rem;font-weight:700;font-family:var(--font-ui);cursor:pointer;
}
.trk-help{text-align:center;margin-top:16px;font-size:.84rem;color:var(--ink-soft)}
.trk-help a{color:var(--emerald);font-weight:700}
</style>
@endpush

@section('content')
<div class="trk">
  <div class="trk-head">
    <a href="{{ route('storefront.home') }}" class="back" aria-label="رجوع">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>
    <h1>تتبّع طلبك</h1>
    <p>أدخل رقم الطلب وموبايلك لتشوف الحالة فورًا</p>
  </div>

  <div class="trk-card">
    <h2>بيانات الطلب</h2>
    <form action="{{ route('storefront.track.search') }}" method="POST">
      @csrf
      <div class="field">
        <label for="number">رقم الطلب</label>
        <input type="text" id="number" name="number" value="{{ old('number') }}" required
               placeholder="ORD-2026-000001" autocomplete="off" style="text-transform:uppercase">
        <p class="field-hint">الرقم اللي وصلك بعد تأكيد الطلب</p>
        @error('number')<p style="color:var(--clay);font-size:.8rem;margin-top:4px">{{ $message }}</p>@enderror
      </div>
      <div class="field">
        <label for="phone">رقم الموبايل</label>
        <input type="tel" id="phone" name="phone" class="phone" value="{{ old('phone') }}" required placeholder="01xxxxxxxxx" inputmode="tel">
        <p class="field-hint">نفس الرقم وقت الطلب</p>
        @error('phone')<p style="color:var(--clay);font-size:.8rem;margin-top:4px">{{ $message }}</p>@enderror
      </div>
      <button type="submit" class="trk-btn">عرض حالة الطلب</button>
    </form>
    <p class="trk-help">
      محتاج مساعدة؟
      <a href="{{ \App\Support\ShopSettings::whatsappUrl('محتاج أتابع طلبي') }}" target="_blank" rel="noopener">واتساب</a>
    </p>
  </div>
</div>
@endsection
