<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', $shop['name'] . ' — ' . $shop['tagline'])</title>
<meta name="description" content="@yield('description', $shop['description'])">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=El+Messiri:wght@400;500;600;700&family=Reem+Kufi:wght@400;500;600;700&display=swap" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
/* ═══ ATTAR — Dark Luxury Design System ═══ */
:root{
  --ink:#f0e4cc;           /* primary text — warm cream */
  --ink-soft:#9a8060;      /* secondary text — muted amber */
  --parchment:#0b0703;     /* main bg — near black */
  --parchment-2:#160e06;   /* elevated bg */
  --gold:#c49020;          /* gold */
  --gold-deep:#e8b840;     /* bright gold for dark surfaces */
  --clay:#9c4a2a;          /* ember accent */
  --olive:#7a8040;         /* sage */
  --green-wa:#128c3e;
  --card:#1c1208;          /* card bg */
  --hair:#2e1e0e;          /* border — very dark */
  --shadow:0 18px 50px -20px rgba(0,0,0,.9);
  --radius:16px;
}
*{margin:0;padding:0;box-sizing:border-box}
html{scroll-behavior:smooth}
body{
  font-family:'El Messiri',sans-serif;color:var(--ink);
  background:radial-gradient(1200px 600px at 80% -10%,rgba(196,144,32,.07),transparent 55%),var(--parchment);
  overflow-x:hidden;min-height:100vh;
}
img{display:block;max-width:100%}
a{color:inherit;text-decoration:none}
input,textarea,select{font-family:'El Messiri',sans-serif;background:var(--card);color:var(--ink);border-color:var(--hair)}
button{font-family:'El Messiri',sans-serif;cursor:pointer}
.wrap{max-width:1200px;margin:0 auto;padding:0 20px;position:relative;z-index:1}

/* ── Header ── */
header.top{
  position:sticky;top:0;z-index:60;
  backdrop-filter:blur(20px) saturate(1.4);
  background:linear-gradient(180deg,rgba(7,4,1,.96),rgba(7,4,1,.88));
  border-bottom:1px solid rgba(196,144,32,.14);
}
.top .wrap{display:flex;align-items:center;justify-content:space-between;height:68px;gap:12px}
.brand{display:flex;align-items:center;gap:11px;font-family:'Reem Kufi';font-weight:700;text-decoration:none}
.brand-seal,.brand-logo{width:42px;height:42px;border-radius:50%;flex-shrink:0}
.brand-seal{
  background:radial-gradient(circle at 35% 35%,#2a1a08,#0a0603);
  display:grid;place-items:center;
  color:var(--gold-deep);font-size:1.3rem;font-family:'Amiri';
  border:1.5px solid rgba(196,144,32,.4);
  box-shadow:0 0 20px -5px rgba(196,144,32,.25);
}
.brand-logo{object-fit:cover;border:1.5px solid rgba(196,144,32,.35)}
.brand b{font-size:1.08rem;color:var(--ink)}
.brand span{display:block;font-size:.63rem;color:var(--gold);font-weight:500;margin-top:-1px;font-family:'El Messiri';letter-spacing:.5px}
.top-actions{display:flex;gap:10px;align-items:center}
.wa-top{
  display:flex;align-items:center;gap:7px;
  background:rgba(18,140,62,.15);color:#4ade80;
  border:1px solid rgba(18,140,62,.3);
  padding:9px 16px;border-radius:40px;font-weight:600;font-size:.88rem;
  text-decoration:none;transition:.25s;
}
.wa-top:hover{background:rgba(18,140,62,.25);border-color:rgba(18,140,62,.6)}
.wa-top svg{width:17px;height:17px}
.cart-btn{
  display:flex;align-items:center;gap:7px;cursor:pointer;
  background:linear-gradient(135deg,var(--gold),var(--gold-deep));
  color:#0b0703;border:none;padding:9px 16px;border-radius:40px;
  font-weight:700;font-size:.88rem;text-decoration:none;
  transition:.25s;box-shadow:0 4px 16px -4px rgba(196,144,32,.45);
}
.cart-btn:hover{box-shadow:0 8px 24px -4px rgba(196,144,32,.6);transform:translateY(-1px)}
.cart-btn .badge{
  background:#0b0703;color:var(--gold-deep);
  min-width:20px;height:20px;border-radius:50%;
  display:grid;place-items:center;font-weight:700;font-size:.75rem;padding:0 4px;
}

/* ── Flash ── */
.flash{padding:14px 20px;border-radius:12px;margin:12px auto;max-width:800px;font-weight:600;text-align:center}
.flash-success{background:rgba(6,95,70,.25);color:#6ee7b7;border:1px solid rgba(110,231,183,.25)}
.flash-error{background:rgba(127,29,29,.25);color:#fca5a5;border:1px solid rgba(252,165,165,.2)}

/* ── Footer ── */
footer.site-footer{
  background:#040201;
  border-top:1px solid rgba(196,144,32,.1);
  color:rgba(240,228,204,.65);padding:52px 0 28px;margin-top:0;
}
footer.site-footer .foot-inner{display:flex;flex-wrap:wrap;gap:36px;justify-content:space-between;margin-bottom:36px}
footer.site-footer h4{font-family:'Reem Kufi';font-size:.88rem;color:var(--gold-deep);margin-bottom:14px;letter-spacing:1px}
footer.site-footer p,footer.site-footer a{font-size:.85rem;color:rgba(240,228,204,.5);line-height:1.9;display:block;transition:.2s}
footer.site-footer a:hover{color:var(--gold-deep);opacity:1}
.foot-copy{
  border-top:1px solid rgba(196,144,32,.08);padding-top:22px;text-align:center;
  font-size:.76rem;color:rgba(196,144,32,.3);letter-spacing:.5px;
}

/* ── Utilities ── */
.btn-primary{
  background:linear-gradient(135deg,var(--gold),var(--gold-deep));
  color:#0b0703;border:none;padding:12px 28px;border-radius:11px;
  font-weight:700;font-size:.96rem;cursor:pointer;transition:.25s;
  display:inline-flex;align-items:center;gap:8px;
  box-shadow:0 6px 24px -6px rgba(196,144,32,.5);
}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 12px 32px -6px rgba(196,144,32,.6)}
.btn-outline{
  background:transparent;color:var(--ink);
  border:1.5px solid rgba(240,228,204,.18);
  padding:10px 24px;border-radius:11px;
  font-weight:600;font-size:.92rem;cursor:pointer;transition:.25s;
  display:inline-flex;align-items:center;gap:8px;
}
.btn-outline:hover{border-color:var(--gold);color:var(--gold-deep)}

@media(max-width:600px){
  .top .wrap{height:60px}
  .brand b{font-size:.9rem}
  .wa-top{padding:8px 12px;font-size:.8rem}
  .cart-btn{padding:8px 12px;font-size:.8rem}
}
</style>
@stack('head-styles')
</head>
<body>

<header class="top">
  <div class="wrap">
    <a href="{{ route('storefront.home') }}" class="brand">
      @if($shop['logo_url'])
        <img src="{{ $shop['logo_url'] }}" alt="{{ $shop['name'] }}" class="brand-logo">
      @else
        <div class="brand-seal">ع</div>
      @endif
      <div>
        <b>{{ $shop['name'] }}</b>
        <span>{{ $shop['tagline'] }}</span>
      </div>
    </a>

    <nav class="top-actions">
      <a href="{{ \App\Support\ShopSettings::whatsappUrl() }}" class="wa-top" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
          <path d="M12 0C5.373 0 0 5.373 0 12c0 2.07.527 4.02 1.448 5.724L0 24l6.437-1.426C8.1 23.467 10.009 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818c-1.848 0-3.568-.497-5.05-1.364l-.362-.215-3.792.84.854-3.698-.236-.38C2.59 15.395 2.182 13.74 2.182 12 2.182 6.582 6.582 2.182 12 2.182S21.818 6.582 21.818 12 17.418 21.818 12 21.818z"/>
        </svg>
        واتساب
      </a>
      <a href="{{ route('storefront.cart') }}" class="cart-btn">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        السلة
        <span class="badge" id="cart-badge" @if(count(session('storefront_cart', [])) === 0) style="display:none" @endif>{{ count(session('storefront_cart', [])) }}</span>
      </a>
    </nav>
  </div>
</header>

@if(session('success'))
  <div class="flash flash-success wrap">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="flash flash-error wrap">{{ session('error') }}</div>
@endif

@yield('content')

<footer class="site-footer">
  <div class="wrap">
    <div class="foot-inner">
      <div>
        <h4>{{ $shop['name'] }}</h4>
        <p>{{ $shop['description'] }}</p>
        <p style="margin-top:8px">توصيل {{ $shop['governorate'] }}</p>
      </div>
      <div>
        <h4>روابط سريعة</h4>
        <a href="{{ route('storefront.home') }}">الرئيسية</a>
        <a href="{{ route('storefront.catalog') }}">المنتجات</a>
        <a href="{{ route('storefront.offers') }}">العروض</a>
        <a href="{{ route('storefront.track.lookup') }}">تتبّع الطلب</a>
        <a href="{{ route('storefront.cart') }}">السلة</a>
      </div>
      <div>
        <h4>تواصل معنا</h4>
        <a href="{{ \App\Support\ShopSettings::whatsappUrl() }}" target="_blank">واتساب</a>
        @if($shop['phone'])
          <a href="tel:{{ $shop['phone'] }}">{{ $shop['phone'] }}</a>
        @endif
        <p>{{ $shop['address'] }}</p>
      </div>
    </div>
    <div class="foot-copy">
      &copy; {{ date('Y') }} {{ $shop['name'] }} — {{ $shop['footer_note'] }}
    </div>
  </div>
</footer>

<div class="store-toast" id="store-toast">
  <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
  <span id="store-toast-msg"></span>
</div>

<style>
.store-toast{
  position:fixed;bottom:28px;left:50%;transform:translateX(-50%) translateY(140px);z-index:200;
  background:rgba(14,8,4,.94);backdrop-filter:blur(16px);
  color:#f0e4cc;padding:13px 24px;border-radius:40px;font-weight:600;font-size:.9rem;
  border:1px solid rgba(196,144,32,.25);
  box-shadow:0 20px 50px -10px rgba(0,0,0,.8),0 0 0 1px rgba(196,144,32,.1);
  transition:transform .38s cubic-bezier(.2,1.3,.4,1);
  display:flex;gap:9px;align-items:center;
}
.store-toast.show{transform:translateX(-50%) translateY(0)}
.store-toast svg{width:18px;height:18px;color:#e8b840}
</style>

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.8/dist/cdn.min.js"></script>
<script>
function productCard(product) {
    return {
        product,
        selectedVariantId: product.default_variant_id || product.variants[0]?.id,
        weightIdx: 0,
        pieceQty: 1,
        loading: false,
        justAdded: false,
        weights: [
            { g: 100, label: '١٠٠ جم' },
            { g: 250, label: '٢٥٠ جم' },
            { g: 500, label: 'نص كيلو' },
            { g: 1000, label: 'كيلو' },
        ],

        get variant() {
            return this.product.variants.find(v => v.id === this.selectedVariantId) || this.product.variants[0];
        },

        get canAdd() {
            return this.variant && this.variant.available > 0;
        },

        init() {
            const v = this.variant;
            if (v) this.pieceQty = v.step || 1;
        },

        selectVariant(id) {
            this.selectedVariantId = id;
            const v = this.variant;
            if (v) this.pieceQty = v.step || 1;
        },

        priceLabel() {
            const v = this.variant;
            if (!v) return '—';
            return v.price_fmt;
        },

        unitSuffix() {
            const v = this.variant;
            if (!v) return '';
            return v.is_weighted ? '/ كجم' : ('/ ' + v.label);
        },

        weightPrice(g) {
            const v = this.variant;
            if (!v) return '';
            const minor = Math.round(v.price_minor * g / 1000);
            return this.fmt(minor) + ' ج';
        },

        stockClass() {
            const av = this.variant?.available ?? 0;
            if (av <= 0) return 'no';
            if (this.variant?.is_weighted ? av < 500 : av < 5) return 'low';
            return 'ok';
        },

        stockLabel() {
            const v = this.variant;
            if (!v || v.available <= 0) return 'نفد';
            if (v.is_weighted) {
                return v.available >= 1000
                    ? (v.available / 1000).toFixed(1) + ' كجم متوفر'
                    : v.available + ' جم متوفر';
            }
            return Math.floor(v.available) + ' متوفر';
        },

        orderQty() {
            const v = this.variant;
            if (!v) return 0;
            if (v.is_weighted) return this.weights[this.weightIdx]?.g ?? v.step;
            return this.pieceQty;
        },

        async addToCart() {
            if (!this.canAdd || this.loading) return;
            const v = this.variant;
            const qty = this.orderQty();
            if (qty > v.available) {
                storeToast('الكمية تتجاوز المتوفر');
                return;
            }
            this.loading = true;
            try {
                const res = await fetch('{{ route('storefront.cart.add') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ variant_id: v.id, qty }),
                });
                const data = await res.json();
                if (!res.ok || !data.ok) throw new Error(data.message || 'تعذّر الإضافة');
                const badge = document.getElementById('cart-badge');
                if (badge) {
                    badge.textContent = data.cart_count;
                    badge.style.display = data.cart_count > 0 ? '' : 'none';
                }
                this.justAdded = true;
                storeToast(this.product.name + ' أُضيف للسلة');
                setTimeout(() => this.justAdded = false, 1500);
            } catch (e) {
                storeToast(e.message || 'حدث خطأ');
            } finally {
                this.loading = false;
            }
        },

        fmt(minor) {
            return ((minor || 0) / 100).toLocaleString('ar-EG', { minimumFractionDigits: 0, maximumFractionDigits: 2 });
        },
    };
}

let storeToastTimer;
function storeToast(msg) {
    const t = document.getElementById('store-toast');
    document.getElementById('store-toast-msg').textContent = msg;
    t.classList.add('show');
    clearTimeout(storeToastTimer);
    storeToastTimer = setTimeout(() => t.classList.remove('show'), 2200);
}
</script>

@stack('scripts')
</body>
</html>
