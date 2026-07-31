@extends('layouts.storefront')

@section('title', 'السلة — ' . $shop['name'])

@push('head-styles')
<style>
/* ══ App-like cart shell ══ */
.app-cart{max-width:560px;margin:0 auto;min-height:calc(100svh - var(--chrome-h));padding-bottom:calc(96px + env(safe-area-inset-bottom,0px));position:relative}
.app-cart-head{display:flex;align-items:center;gap:12px;padding:16px 4px 12px}
.app-cart-head a.back{
  width:42px;height:42px;border-radius:14px;display:grid;place-items:center;
  background:var(--card);border:1px solid var(--hair);color:var(--ink);flex-shrink:0;
}
.app-cart-head h1{font-family:var(--font-thuluth);font-size:1.55rem;font-weight:400;line-height:1.3;flex:1;min-width:0}
.app-cart-head .count{font-family:var(--font-ui);font-size:.78rem;color:var(--ink-soft);background:var(--parchment-2);padding:6px 12px;border-radius:20px}
.money{unicode-bidi:isolate;direction:ltr;display:inline-block}

.app-empty{text-align:center;padding:72px 20px 40px;color:var(--ink-soft)}
.app-empty .ico{
  width:88px;height:88px;margin:0 auto 18px;border-radius:28px;
  background:linear-gradient(145deg,#1a1510,#0c0a08);color:var(--gold-light);
  display:grid;place-items:center;font-size:2rem;
  box-shadow:0 16px 40px -12px rgba(12,10,8,.35);
}
.app-empty p{font-size:1.05rem;margin-bottom:22px;line-height:1.7}

.app-lines{display:flex;flex-direction:column;gap:12px;padding:4px 0 8px}
.app-line{
  display:grid;grid-template-columns:72px minmax(0,1fr);gap:12px;
  background:var(--card);border:1px solid var(--hair);border-radius:18px;padding:12px;
  box-shadow:0 8px 28px -18px rgba(12,10,8,.2);
  transition:opacity .2s,transform .2s;
}
.app-line.is-busy{opacity:.55;pointer-events:none}
.app-line img,.app-line .ph{
  width:72px;height:72px;border-radius:14px;object-fit:cover;object-position:center;
  background:linear-gradient(165deg,#e8efeb,#d4ddd8);flex-shrink:0;
}
.app-line .ph{display:grid;place-items:center;font-size:1.6rem;color:var(--copper)}
.app-line-body{min-width:0;display:flex;flex-direction:column;gap:8px}
.app-line-top{display:flex;align-items:flex-start;gap:8px}
.app-line-name{font-weight:700;font-size:.95rem;line-height:1.4;flex:1;min-width:0;word-break:break-word}
.app-line-del{
  width:34px;height:34px;border:none;border-radius:10px;background:var(--parchment-2);
  color:#999;font-size:1.15rem;cursor:pointer;flex-shrink:0;
}
.app-line-del:active{background:#fee2e2;color:var(--clay)}
.app-line-meta{font-size:.78rem;color:var(--ink-soft)}
.app-line-foot{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-top:auto}
.app-stepper{
  display:inline-flex;align-items:center;gap:0;
  background:var(--parchment-2);border:1px solid var(--hair);border-radius:14px;padding:3px;
}
.app-stepper button{
  width:40px;height:40px;border:none;border-radius:11px;background:var(--card);
  font-size:1.25rem;font-weight:700;cursor:pointer;color:var(--ink);
  box-shadow:0 1px 3px rgba(12,10,8,.06);
}
.app-stepper button:active{transform:scale(.94)}
.app-stepper .qty{
  min-width:52px;text-align:center;font-weight:800;font-size:1rem;padding:0 4px;
  font-variant-numeric:tabular-nums;
}
.app-stepper .unit{font-size:.68rem;color:var(--ink-soft);padding-inline-end:6px}
.app-line-total{font-weight:800;font-size:1.05rem;color:var(--gold-deep);white-space:nowrap}

.app-sheet{
  background:var(--card);border:1px solid var(--hair);border-radius:20px;padding:16px;
  margin-top:16px;box-shadow:0 8px 28px -18px rgba(12,10,8,.18);
}
.app-sheet h3{font-family:var(--font-ui);font-size:.92rem;margin-bottom:12px}
.app-sum{display:flex;justify-content:space-between;gap:8px;padding:8px 0;font-size:.9rem;border-bottom:1px solid var(--hair)}
.app-sum:last-of-type{border-bottom:none}
.app-sum.muted span:first-child{color:var(--ink-soft)}
.app-sum.grand{font-weight:800;font-size:1.05rem;padding-top:12px;margin-top:4px;border-top:2px solid var(--hair)}
.app-coupon{display:flex;gap:8px;margin-top:12px}
.app-coupon input{
  flex:1;min-width:0;padding:13px 14px;border:1.5px solid var(--hair);border-radius:12px;
  font-size:16px;background:var(--parchment-2);
}
.app-coupon button{
  padding:13px 16px;border:none;border-radius:12px;background:var(--ink);color:var(--gold-light);
  font-weight:700;cursor:pointer;white-space:nowrap;
}
.app-coupon-ok{
  display:flex;justify-content:space-between;align-items:center;gap:8px;
  background:#e8f5e9;color:#1b5e20;padding:12px;border-radius:12px;margin-top:12px;font-weight:600;font-size:.88rem;
}
.app-coupon-ok button{background:none;border:none;color:#1b5e20;font-weight:700;cursor:pointer}

.app-dock{
  position:fixed;inset-inline:0;bottom:0;z-index:90;
  background:#ffffff;
  border-top:1px solid var(--hair);
  padding:12px 16px;padding-bottom:calc(12px + env(safe-area-inset-bottom,0px));
  box-shadow:0 -12px 40px -12px rgba(11,22,18,.28);
}
.app-dock-inner{max-width:560px;margin:0 auto;display:flex;align-items:center;gap:12px}
.app-dock .tot{flex-shrink:0}
.app-dock .tot small{display:block;font-size:.7rem;color:var(--ink-soft);font-family:var(--font-ui)}
.app-dock .tot strong{font-size:1.2rem;color:var(--gold-deep)}
.app-dock .cta{
  flex:1;display:flex;align-items:center;justify-content:center;gap:8px;
  min-height:52px;padding:14px 16px;border-radius:16px;border:none;
  background:#0c0a08;color:var(--gold-light);
  font-weight:800;font-size:1rem;text-decoration:none;
  box-shadow:0 12px 28px -10px rgba(12,10,8,.45);
  opacity:1;
}
.app-dock .cta:active{transform:scale(.98)}

@media(min-width:900px){
  .app-cart{max-width:720px;padding-top:8px}
  .app-lines{gap:14px}
}
</style>
@endpush

@section('content')
@php
  $taxMinor = (int) round($total - ($total / 1.14));
  $subtotalFmt = number_format($subtotal / 100, 2);
  $taxFmt = number_format($taxMinor / 100, 2);
  $totalFmt = number_format($total / 100, 2);
  $linesPayload = collect($cart)->map(fn ($l) => [
      'variant_id' => $l['variant_id'],
      'name' => $l['name'],
      'qty' => (float) $l['qty'],
      'step' => (float) ($l['step'] ?? 1),
      'is_weighted' => (bool) $l['is_weighted'],
      'unit_label' => $l['unit_label'],
      'price_minor' => (int) $l['price_minor'],
      'line_total_minor' => (int) $l['line_total_minor'],
      'image' => $l['image'] ?? null,
      'available' => isset($l['available']) ? (float) $l['available'] : null,
  ])->values()->all();
@endphp

<div class="wrap app-cart" x-data="appCart(@js($linesPayload), {{ (int) $subtotal }}, {{ (int) $discountMinor }}, {{ (int) $total }})" x-cloak>

  <div class="app-cart-head">
    <a href="{{ route('storefront.catalog') }}" class="back" aria-label="رجوع">
      <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
    </a>
    <h1>سلتي</h1>
    <span class="count" x-text="lines.length + ' أصناف'"></span>
  </div>

  @if(!empty($stockNotice))
    <div class="store-alert" role="status" style="margin:0 0 14px">
      <div class="store-alert-head">
        <div class="store-alert-ico" aria-hidden="true">
          <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
          </svg>
        </div>
        <div>
          <h3>{{ $stockNotice['title'] }}</h3>
          <p>{{ $stockNotice['body'] }}</p>
          @if(!empty($stockNotice['items']))
            <ul>
              @foreach($stockNotice['items'] as $item)
                <li>{{ $item }}</li>
              @endforeach
            </ul>
          @endif
        </div>
      </div>
    </div>
  @endif

  <template x-if="!lines.length">
    <div class="app-empty">
      <div class="ico">🛍</div>
      <p>سلتك فارغة — ابدأ بإضافة بهاراتك المفضّلة</p>
      <a href="{{ route('storefront.catalog') }}" class="btn-primary">تصفّح المنتجات</a>
    </div>
  </template>

  <template x-if="lines.length">
    <div>
      <div class="app-lines">
        <template x-for="line in lines" :key="line.variant_id">
          <article class="app-line" :class="busyId === line.variant_id && 'is-busy'">
            <template x-if="line.image">
              <img :src="line.image" alt="" width="72" height="72">
            </template>
            <template x-if="!line.image">
              <div class="ph">ع</div>
            </template>
            <div class="app-line-body">
              <div class="app-line-top">
                <div class="app-line-name" x-text="line.name"></div>
                <button type="button" class="app-line-del" @click="setQty(line, 0)" aria-label="حذف">×</button>
              </div>
              <div class="app-line-meta">
                <span class="money" x-text="fmt(line.price_minor)"></span> ج.م /
                <span x-text="line.is_weighted ? 'كجم' : line.unit_label"></span>
              </div>
              <div class="app-line-foot">
                <div class="app-stepper">
                  <button type="button" @click="bump(line, -1)" aria-label="تقليل">−</button>
                  <span class="qty" x-text="line.is_weighted ? Math.round(line.qty) : line.qty"></span>
                  <span class="unit" x-text="line.is_weighted ? 'جم' : line.unit_label"></span>
                  <button type="button" @click="bump(line, 1)" aria-label="زيادة">+</button>
                </div>
                <div class="app-line-total"><span class="money" x-text="fmt(line.line_total_minor)"></span> ج.م</div>
              </div>
            </div>
          </article>
        </template>
      </div>

      <div class="app-sheet">
        <h3>ملخص الطلب</h3>
        <div class="app-sum muted"><span>المجموع الفرعي</span><span><span class="money" x-text="fmt(subtotal)"></span> ج.م</span></div>
        <template x-if="discount > 0">
          <div class="app-sum muted"><span>الخصم</span><span style="color:var(--olive)">− <span class="money" x-text="fmt(discount)"></span> ج.م</span></div>
        </template>
        <div class="app-sum muted"><span>ض.ق.م 14%</span><span><span class="money" x-text="fmt(tax)"></span> ج.م</span></div>
        <div class="app-sum grand"><span>الإجمالي</span><strong><span class="money" x-text="fmt(total)"></span> ج.م</strong></div>

        @if($coupon)
          <div class="app-coupon-ok">
            <span>✓ {{ $coupon }}</span>
            <form action="{{ route('storefront.cart.coupon') }}" method="POST">
              @csrf
              <button type="submit" name="coupon" value="">إزالة</button>
            </form>
          </div>
        @else
          <form action="{{ route('storefront.cart.coupon') }}" method="POST" class="app-coupon">
            @csrf
            <input type="text" name="coupon" placeholder="كوبون خصم" style="text-transform:uppercase" autocomplete="off">
            <button type="submit">تطبيق</button>
          </form>
        @endif
      </div>
    </div>
  </template>

  <template x-if="lines.length">
    <div class="app-dock">
      <div class="app-dock-inner">
        <div class="tot">
          <small>الإجمالي</small>
          <strong><span class="money" x-text="fmt(total)"></span> ج.م</strong>
        </div>
        <a href="{{ route('storefront.checkout') }}" class="cta">متابعة الشراء</a>
      </div>
    </div>
  </template>
</div>
@endsection

@push('scripts')
<script>
function appCart(initialLines, subtotal, discount, total) {
  return {
    lines: initialLines,
    subtotal,
    discount,
    total,
    busyId: null,
    get tax() {
      return Math.round(this.total - (this.total / 1.14));
    },
    fmt(minor) {
      return ((minor || 0) / 100).toLocaleString('ar-EG', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
    },
    bump(line, dir) {
      const step = line.step || 1;
      let next = +(line.qty + dir * step).toFixed(3);
      if (dir > 0 && line.available != null && next > line.available) {
        next = line.available;
        if (typeof storeToast === 'function') {
          storeToast('وصلتَ للحد الأقصى المتاح من هذا الصنف.');
        }
        if (next <= line.qty) return;
      }
      this.setQty(line, next <= 0 ? 0 : next);
    },
    async setQty(line, qty) {
      if (this.busyId) return;
      this.busyId = line.variant_id;
      try {
        const res = await fetch(@js(route('storefront.cart.update')), {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
          },
          body: JSON.stringify({ variant_id: line.variant_id, qty }),
        });
        const data = await res.json();
        if (!res.ok || !data.ok) throw new Error(data.message || 'تعذّر التحديث');

        const badge = document.getElementById('cart-badge');
        if (badge) {
          badge.textContent = data.cart_count ?? 0;
          badge.style.display = (data.cart_count ?? 0) > 0 ? '' : 'none';
        }

        if (data.removed || qty <= 0) {
          this.lines = this.lines.filter(l => l.variant_id !== line.variant_id);
        } else if (data.line) {
          const i = this.lines.findIndex(l => l.variant_id === line.variant_id);
          if (i >= 0) this.lines[i] = { ...this.lines[i], ...data.line };
        }

        if (typeof data.subtotal_minor === 'number') {
          this.subtotal = data.subtotal_minor;
          this.total = Math.max(0, this.subtotal - this.discount);
        } else {
          this.recalcLocal();
        }
      } catch (e) {
        storeToast(e.message || 'حدث خطأ');
      } finally {
        this.busyId = null;
      }
    },
    recalcLocal() {
      this.subtotal = this.lines.reduce((s, l) => s + (l.line_total_minor || 0), 0);
      this.total = Math.max(0, this.subtotal - this.discount);
    },
  };
}
</script>
@endpush
