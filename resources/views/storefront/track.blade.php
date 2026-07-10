@extends('layouts.storefront')

@section('title', 'تتبّع الطلب #' . $order->number . ' — عبد القادر العطّار')

@push('head-styles')
<style>
.page-title{font-family:'Amiri';font-size:2rem;font-weight:700;padding:28px 0 8px}
.order-number{font-family:'Reem Kufi';color:var(--gold-deep);font-size:1rem;margin-bottom:28px}

.track-layout{display:grid;grid-template-columns:1fr 360px;gap:28px;padding-bottom:60px;align-items:start}
@media(max-width:780px){.track-layout{grid-template-columns:1fr}}

/* Status hero */
.status-hero{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);
  padding:28px;text-align:center;margin-bottom:24px}
.status-icon{width:72px;height:72px;border-radius:50%;margin:0 auto 12px;
  display:flex;align-items:center;justify-content:center;font-size:2rem}
.status-label{font-family:'Reem Kufi';font-size:1.5rem;font-weight:700}
.status-sub{color:var(--ink-soft);font-size:.9rem;margin-top:4px}

/* Progress steps */
.progress-track{display:flex;justify-content:space-between;align-items:center;
  margin:28px 0;position:relative;padding:0 8px}
.progress-track::before{content:'';position:absolute;top:50%;left:8px;right:8px;height:3px;
  background:var(--hair);transform:translateY(-50%);z-index:0}
.progress-fill{position:absolute;top:50%;left:8px;height:3px;
  background:var(--gold-deep);transform:translateY(-50%);z-index:1;transition:width .5s}
.step{display:flex;flex-direction:column;align-items:center;gap:6px;z-index:2;position:relative}
.step-dot{width:30px;height:30px;border-radius:50%;border:3px solid var(--hair);
  background:var(--parchment);display:flex;align-items:center;justify-content:center;font-size:.8rem;transition:.3s}
.step.done .step-dot{background:var(--gold-deep);border-color:var(--gold-deep);color:#fff}
.step.current .step-dot{background:var(--olive);border-color:var(--olive);color:#fff}
.step-name{font-size:.72rem;font-weight:600;color:var(--ink-soft);text-align:center;max-width:60px}
.step.done .step-name,.step.current .step-name{color:var(--ink)}

/* Timeline */
.timeline-card{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);padding:24px}
.timeline-card h3{font-family:'Reem Kufi';font-size:1rem;font-weight:700;margin-bottom:18px}
.timeline{position:relative;padding-right:24px}
.timeline::before{content:'';position:absolute;right:6px;top:0;bottom:0;width:2px;background:var(--hair)}
.tl-item{position:relative;padding-bottom:18px}
.tl-item::last-child{padding-bottom:0}
.tl-dot{position:absolute;right:-18px;top:4px;width:12px;height:12px;border-radius:50%;
  background:var(--gold-deep);border:2px solid var(--parchment);box-shadow:0 0 0 2px var(--gold-deep)}
.tl-meta{font-size:.78rem;color:var(--ink-soft);margin-bottom:2px}
.tl-label{font-weight:700;font-size:.92rem}
.tl-note{font-size:.82rem;color:var(--ink-soft)}

/* Summary card */
.order-card{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);padding:24px;position:sticky;top:100px}
.order-card h3{font-family:'Reem Kufi';font-size:1.05rem;font-weight:700;margin-bottom:16px}
.order-line{display:flex;justify-content:space-between;align-items:start;padding:9px 0;
  border-bottom:1px solid var(--hair);font-size:.88rem;gap:8px}
.order-line:last-child{border-bottom:none}
.order-line-name{color:var(--ink-soft);flex:1}
.order-line-price{font-family:'Reem Kufi';font-weight:700;color:var(--gold-deep);flex-shrink:0}
.total-section{margin-top:16px;padding-top:14px;border-top:2px solid var(--hair)}
.total-row{display:flex;justify-content:space-between;font-size:.88rem;margin-bottom:6px}
.total-row.big{font-size:1.1rem;font-weight:800;margin-top:8px}

/* Address */
.address-box{background:var(--parchment-2);border:1px solid var(--hair);border-radius:10px;padding:14px;
  font-size:.88rem;color:var(--ink-soft);line-height:1.7;margin-top:12px}
.address-box strong{color:var(--ink);display:block;margin-bottom:2px;font-size:.92rem}

.wa-help{display:flex;align-items:center;gap:10px;background:#d1fae5;border:1px solid #6ee7b7;
  border-radius:12px;padding:14px;margin-top:18px;text-decoration:none;color:#065f46;font-weight:600;font-size:.9rem}

.success-banner{background:linear-gradient(135deg,#d1fae5,#a7f3d0);border:2px solid #6ee7b7;
  border-radius:var(--radius);padding:22px 24px;margin-bottom:24px;text-align:center}
.success-banner h2{font-family:'Reem Kufi';font-size:1.35rem;font-weight:700;color:#065f46;margin-bottom:6px}
.success-banner p{color:#047857;font-size:.92rem;margin-bottom:14px}
.wa-notify-btn{display:inline-flex;align-items:center;gap:8px;background:#128c3e;color:#fff;
  padding:12px 22px;border-radius:40px;font-weight:700;text-decoration:none;font-size:.95rem}
.wa-notify-btn:hover{background:#0e6e31}
</style>
@endpush

@section('content')
<div class="wrap">
  @if(session('success'))
  <div class="success-banner">
    <h2>✅ {{ session('success') }}</h2>
    <p>سيتم التواصل معك قريبًا لتأكيد الطلب.</p>
    @php
      $payMethod = $order->shipping_address['payment_method'] ?? null;
      $payNum = $payMethod ? \App\Support\StorefrontCheckout::paymentNumber($payMethod) : null;
    @endphp
    @if($payMethod && $payMethod !== 'cod' && $payNum)
    <div style="background:#fef3c7;border-radius:10px;padding:12px 16px;margin:12px 0;font-weight:600">
      حوّل <strong>{{ $order->total_minor->format() }}</strong> على
      {{ \App\Support\StorefrontCheckout::paymentLabel($payMethod) }}:
      <div style="font-family:'Reem Kufi';font-size:1.15rem;direction:ltr;margin-top:6px">{{ $payNum }}</div>
    </div>
    @endif
    @if(session('whatsapp_notify'))
    <a href="{{ session('whatsapp_notify') }}" target="_blank" rel="noopener" class="wa-notify-btn" id="wa-notify-link">
      <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413z"/></svg>
      إرسال الطلب للمتجر عبر واتساب
    </a>
    @endif
  </div>
  @endif

  <h1 class="page-title">تتبّع طلبك</h1>
  <p class="order-number">رقم الطلب: {{ $order->number }}</p>

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
  @endphp

  <div class="track-layout">
    <div>
      {{-- Status hero --}}
      <div class="status-hero">
        <div class="status-icon"
             style="background:{{ $currentStatus === 'cancelled' ? '#fee2e2' : ($currentStatus === 'delivered' ? '#d1fae5' : '#fef3c7') }}">
          {{ $currentStep['icon'] }}
        </div>
        <div class="status-label">{{ $currentStep['label'] }}</div>
        <div class="status-sub">
          {{ $order->placed_at?->format('d/m/Y H:i') ?? '' }}
        </div>

        {{-- Progress bar --}}
        @if($currentIdx >= 0)
        <div class="progress-track">
          <div class="progress-fill" style="width: {{ $progressWidth }}"></div>
          @foreach($stepList as $step)
          @php
            $stepIdx = array_search($step['key'], array_column($stepList, 'key'));
            $isDone = $stepIdx < $currentIdx;
            $isCurrent = $stepIdx === $currentIdx;
          @endphp
          <div class="step {{ $isDone ? 'done' : ($isCurrent ? 'current' : '') }}">
            <div class="step-dot">{{ $isDone ? '✓' : ($isCurrent ? '●' : '') }}</div>
            <span class="step-name">{{ $step['name'] }}</span>
          </div>
          @endforeach
        </div>
        @endif
      </div>

      {{-- Timeline --}}
      <div class="timeline-card">
        <h3>الخط الزمني</h3>
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
          <p style="color:var(--ink-soft);font-size:.88rem">لا توجد سجلات حتى الآن.</p>
          @endforelse
        </div>
      </div>
    </div>

    {{-- Order details --}}
    <div class="order-card">
      <h3>تفاصيل الطلب</h3>

      @foreach($order->lines as $line)
      <div class="order-line">
        <span class="order-line-name">
          {{ $line->name_snapshot }}
          <span style="color:var(--hair)"> × {{ $line->qty }}</span>
        </span>
        <span class="order-line-price">
          {{ $line->line_total_minor->format() }}
        </span>
      </div>
      @endforeach

      <div class="total-section">
        <div class="total-row">
          <span style="color:var(--ink-soft)">المجموع الفرعي</span>
          <span>{{ $order->subtotal_minor->format() }}</span>
        </div>
        @if($order->discount_minor->isPositive())
        <div class="total-row" style="color:var(--olive)">
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
        📞 {{ $order->shipping_address['phone'] ?? '' }}
      </div>
      @endif

      <a href="{{ \App\Support\StorefrontWhatsApp::url('استفسار عن طلب رقم: ' . $order->number) }}"
         target="_blank" class="wa-help">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor">
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
