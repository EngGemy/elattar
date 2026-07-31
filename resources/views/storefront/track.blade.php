@extends('layouts.storefront')

@section('title', 'تتبّع الطلب #' . $order->number . ' — عبد القادر العطّار')

@push('head-styles')
<style>
.trk-app{max-width:720px;margin:0 auto;padding:16px 16px 36px}
.trk-back{
  width:42px;height:42px;border-radius:14px;display:grid;place-items:center;
  background:var(--card);border:1px solid var(--hair);color:var(--ink);margin-bottom:14px;
}
.trk-app h1{font-family:var(--font-thuluth);font-size:1.55rem;font-weight:700}
.trk-num{font-family:var(--font-ui);color:var(--emerald);font-size:.92rem;font-weight:700;margin:4px 0 18px}

.trk-ok{
  background:linear-gradient(145deg,#d8f3e4,#b8e6ce);border:1px solid #6ee7b7;
  border-radius:20px;padding:20px 16px;margin-bottom:16px;text-align:center;
  animation:rise .5s cubic-bezier(.2,.8,.2,1) both;
}
.trk-ok h2{font-family:var(--font-thuluth);font-size:1.25rem;font-weight:700;color:#065f46;margin-bottom:6px}
.trk-ok p{color:#047857;font-size:.88rem;margin-bottom:12px}
.trk-pay{
  background:#fef3c7;border-radius:12px;padding:12px 14px;margin:10px 0;font-weight:600;font-size:.88rem;color:#78350f;
}
.trk-pay .num{font-family:var(--font-ui);font-size:1.1rem;direction:ltr;margin-top:6px;color:var(--night)}
.wa-notify-btn{
  display:inline-flex;align-items:center;gap:8px;background:#128c3e;color:#fff;
  padding:12px 20px;border-radius:14px;font-weight:700;text-decoration:none;font-size:.9rem;font-family:var(--font-ui);
}

.trk-layout{display:grid;gap:14px}
@media(min-width:780px){.trk-layout{grid-template-columns:1.2fr .8fr;align-items:start}}

.trk-card{
  background:var(--card);border:1px solid var(--hair);border-radius:22px;padding:20px 16px;
  box-shadow:0 16px 40px -24px rgba(11,22,18,.28);
  animation:rise .55s cubic-bezier(.2,.8,.2,1) both;
}
.trk-card:nth-child(2){animation-delay:.08s}
@keyframes rise{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}

.status-hero{text-align:center;padding-bottom:4px}
.status-icon{
  width:64px;height:64px;border-radius:20px;margin:0 auto 12px;
  display:grid;place-items:center;font-size:1.6rem;
}
.status-label{font-family:var(--font-thuluth);font-size:1.35rem;font-weight:700}
.status-sub{color:var(--ink-soft);font-size:.82rem;margin-top:4px;font-family:var(--font-ui)}

.progress-track{
  display:flex;justify-content:space-between;align-items:flex-start;
  margin:22px 0 8px;position:relative;padding:0 4px;gap:2px;
}
.progress-track::before{
  content:'';position:absolute;top:14px;left:12%;right:12%;height:3px;
  background:var(--hair);z-index:0;
}
.progress-fill{
  position:absolute;top:14px;right:12%;height:3px;
  background:var(--emerald);z-index:1;transition:width .6s cubic-bezier(.2,.8,.2,1);
}
.step{display:flex;flex-direction:column;align-items:center;gap:6px;z-index:2;flex:1;min-width:0}
.step-dot{
  width:28px;height:28px;border-radius:50%;border:2.5px solid var(--hair);
  background:var(--parchment);display:grid;place-items:center;font-size:.7rem;
  font-family:var(--font-ui);font-weight:700;color:transparent;transition:.3s;
}
.step.done .step-dot{background:var(--emerald);border-color:var(--emerald);color:#fff}
.step.current .step-dot{background:var(--gold);border-color:var(--gold);color:var(--night)}
.step-name{font-size:.65rem;font-weight:600;color:var(--ink-soft);text-align:center;font-family:var(--font-ui)}
.step.done .step-name,.step.current .step-name{color:var(--ink)}

.trk-card h3{font-family:var(--font-ui);font-size:.92rem;font-weight:700;margin-bottom:14px}
.timeline{position:relative;padding-right:22px}
.timeline::before{content:'';position:absolute;right:5px;top:4px;bottom:4px;width:2px;background:var(--hair)}
.tl-item{position:relative;padding-bottom:16px}
.tl-item:last-child{padding-bottom:0}
.tl-dot{
  position:absolute;right:-17px;top:4px;width:12px;height:12px;border-radius:50%;
  background:var(--emerald);border:2px solid var(--card);box-shadow:0 0 0 2px var(--emerald);
}
.tl-meta{font-size:.74rem;color:var(--ink-soft);margin-bottom:2px;font-family:var(--font-ui)}
.tl-label{font-weight:700;font-size:.9rem;font-family:var(--font-ui)}
.tl-note{font-size:.8rem;color:var(--ink-soft);margin-top:2px}

.order-line{
  display:flex;justify-content:space-between;align-items:start;padding:10px 0;
  border-bottom:1px solid var(--hair);font-size:.86rem;gap:8px;
}
.order-line:last-of-type{border-bottom:none}
.order-line-name{color:var(--ink-soft);flex:1;min-width:0;line-height:1.45}
.order-line-price{font-family:var(--font-ui);font-weight:700;color:var(--emerald);flex-shrink:0}
.total-section{margin-top:12px;padding-top:12px;border-top:1.5px solid var(--hair)}
.total-row{display:flex;justify-content:space-between;font-size:.86rem;margin-bottom:6px;font-family:var(--font-ui)}
.total-row.big{font-size:1.05rem;font-weight:800;margin-top:8px;color:var(--emerald)}

.address-box{
  background:var(--parchment-2);border:1px solid var(--hair);border-radius:14px;padding:14px;
  font-size:.86rem;color:var(--ink-soft);line-height:1.7;margin-top:12px;
}
.address-box strong{color:var(--ink);display:block;margin-bottom:2px;font-size:.9rem;font-family:var(--font-ui)}

.wa-help{
  display:flex;align-items:center;gap:10px;background:#d1fae5;border:1px solid #6ee7b7;
  border-radius:14px;padding:14px;margin-top:14px;text-decoration:none;color:#065f46;
  font-weight:700;font-size:.88rem;font-family:var(--font-ui);
}
</style>
@endpush

@section('content')
@php
  $statusSteps = [
      'pending'    => ['label' => 'قيد الانتظار', 'icon' => '⏳', 'idx' => 0],
      'confirmed'  => ['label' => 'مؤكَّد',        'icon' => '✓',  'idx' => 1],
      'processing' => ['label' => 'قيد التجهيز',   'icon' => '⚙',  'idx' => 2],
      'shipped'    => ['label' => 'في الطريق',      'icon' => '🚚', 'idx' => 3],
      'delivered'  => ['label' => 'تم التوصيل',    'icon' => '✅', 'idx' => 4],
      'cancelled'  => ['label' => 'ملغي',           'icon' => '✕',  'idx' => -1],
      'returned'   => ['label' => 'مُرتجَع',         'icon' => '↩',  'idx' => -1],
  ];
  $currentStatus = $order->status instanceof \Spatie\ModelStates\State ? $order->status::$name : (string) $order->status;
  $currentStep   = $statusSteps[$currentStatus] ?? ['label' => $currentStatus, 'icon' => '●', 'idx' => 0];
  $currentIdx    = $currentStep['idx'];
  $progressWidth = $currentIdx < 0 ? 0 : min(100, $currentIdx * 25) . '%';
  $stepList = [
      ['key' => 'pending',    'name' => 'استلام'],
      ['key' => 'confirmed',  'name' => 'تأكيد'],
      ['key' => 'processing', 'name' => 'تجهيز'],
      ['key' => 'shipped',    'name' => 'شحن'],
      ['key' => 'delivered',  'name' => 'توصيل'],
  ];
  $iconBg = $currentStatus === 'cancelled' ? '#fee2e2' : ($currentStatus === 'delivered' ? '#d1fae5' : '#fef3c7');
@endphp

<div class="trk-app">
  <a href="{{ route('storefront.track.lookup') }}" class="trk-back" aria-label="رجوع">
    <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
  </a>

  @if(session('success'))
  <div class="trk-ok">
    <h2>{{ session('success') }}</h2>
    <p>سيتم التواصل معك قريبًا لتأكيد الطلب.</p>
    @php
      $payMethod = $order->shipping_address['payment_method'] ?? null;
      $payNum = $payMethod ? \App\Support\StorefrontCheckout::paymentNumber($payMethod) : null;
    @endphp
    @if($payMethod && $payMethod !== 'cod' && $payNum)
    <div class="trk-pay">
      حوّل <strong>{{ $order->total_minor->format() }}</strong> على
      {{ \App\Support\StorefrontCheckout::paymentLabel($payMethod) }}:
      <div class="num">{{ $payNum }}</div>
    </div>
    @endif
    @if(session('whatsapp_notify'))
    <a href="{{ session('whatsapp_notify') }}" target="_blank" rel="noopener" class="wa-notify-btn" id="wa-notify-link">
      <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/></svg>
      إرسال الطلب للمتجر عبر واتساب
    </a>
    @endif
  </div>
  @endif

  <h1>تتبّع طلبك</h1>
  <p class="trk-num">رقم الطلب: {{ $order->number }}</p>

  <div class="trk-layout">
    <div class="trk-card">
      <div class="status-hero">
        <div class="status-icon" style="background:{{ $iconBg }}">{{ $currentStep['icon'] }}</div>
        <div class="status-label">{{ $currentStep['label'] }}</div>
        <div class="status-sub">{{ $order->placed_at?->format('d/m/Y H:i') ?? '' }}</div>

        @if($currentIdx >= 0)
        <div class="progress-track">
          <div class="progress-fill" style="width: {{ $progressWidth }}"></div>
          @foreach($stepList as $i => $step)
          @php
            $isDone = $i < $currentIdx;
            $isCurrent = $i === $currentIdx;
          @endphp
          <div class="step {{ $isDone ? 'done' : ($isCurrent ? 'current' : '') }}">
            <div class="step-dot">{{ $isDone ? '✓' : ($isCurrent ? '●' : '') }}</div>
            <span class="step-name">{{ $step['name'] }}</span>
          </div>
          @endforeach
        </div>
        @endif
      </div>

      <h3 style="margin-top:18px">الخط الزمني</h3>
      <div class="timeline">
        @forelse($order->statusHistory as $event)
        <div class="tl-item">
          <div class="tl-dot"></div>
          <div class="tl-meta">{{ $event->created_at?->format('d/m/Y H:i') }}</div>
          <div class="tl-label">
            @php $ev = $statusSteps[$event->to_status] ?? null; @endphp
            {{ $ev ? $ev['label'] : $event->to_status }}
          </div>
          @if($event->note)
          <div class="tl-note">{{ $event->note }}</div>
          @endif
        </div>
        @empty
        <p style="color:var(--ink-soft);font-size:.86rem">لا توجد سجلات حتى الآن.</p>
        @endforelse
      </div>
    </div>

    <div class="trk-card">
      <h3>تفاصيل الطلب</h3>
      @foreach($order->lines as $line)
      <div class="order-line">
        <span class="order-line-name">
          {{ $line->name_snapshot }}
          <span style="opacity:.5"> × {{ $line->qty }}</span>
        </span>
        <span class="order-line-price">{{ $line->line_total_minor->format() }}</span>
      </div>
      @endforeach

      <div class="total-section">
        <div class="total-row">
          <span style="color:var(--ink-soft)">المجموع الفرعي</span>
          <span>{{ $order->subtotal_minor->format() }}</span>
        </div>
        @if($order->discount_minor->isPositive())
        <div class="total-row" style="color:var(--emerald)">
          <span>الخصم</span>
          <span>− {{ $order->discount_minor->format() }}</span>
        </div>
        @endif
        <div class="total-row">
          <span style="color:var(--ink-soft)">الضريبة (شاملة)</span>
          <span>{{ $order->tax_minor->format() }}</span>
        </div>
        @if($order->shipping_minor->isPositive())
        <div class="total-row">
          <span style="color:var(--ink-soft)">الشحن</span>
          <span>{{ $order->shipping_minor->format() }}</span>
        </div>
        @endif
        <div class="total-row big">
          <span>الإجمالي</span>
          <strong>{{ $order->total_minor->format() }}</strong>
        </div>
      </div>

      @if($order->shipping_address)
      <div class="address-box">
        <strong>{{ $order->shipping_address['recipient_name'] ?? '' }}</strong>
        {{ $order->shipping_address['street'] ?? '' }}
        @if($order->shipping_address['building'] ?? '') — {{ $order->shipping_address['building'] }} @endif
        — {{ $order->shipping_address['city'] ?? '' }}
        — {{ $order->shipping_address['governorate'] ?? '' }}
        <br>
        {{ $order->shipping_address['phone'] ?? '' }}
      </div>
      @endif

      <a href="{{ \App\Support\StorefrontWhatsApp::url('استفسار عن طلب رقم: ' . $order->number) }}"
         target="_blank" class="wa-help">
        <svg width="18" height="18" viewBox="0 0 24 24" fill="currentColor">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/>
        </svg>
        استفسر عبر واتساب
      </a>
    </div>
  </div>
</div>
@endsection

@push('scripts')
@if(session('whatsapp_notify'))
<script>
document.addEventListener('DOMContentLoaded', function () {
    const link = document.getElementById('wa-notify-link');
    if (link) {
        setTimeout(function () { window.open(link.href, '_blank', 'noopener'); }, 800);
    }
});
</script>
@endif
@endpush
