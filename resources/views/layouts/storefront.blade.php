<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>@yield('title', $shop['name'] . ' — ' . $shop['tagline'])</title>
<meta name="description" content="@yield('description', $shop['description'])">
<link rel="preconnect" href="https://db.onlinewebfonts.com" crossorigin>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=El+Messiri:wght@400;500;600;700&family=Reem+Kufi:wght@400;500;600;700&display=swap" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
<style>
@font-face{
  font-family:'Thuluth';
  src:url('https://db.onlinewebfonts.com/t/4191b42ee098af4b2d29ef55b66483a6.woff2') format('woff2');
  font-weight:normal;font-style:normal;font-display:swap;
}
/* ═══ ATTAR — Sunlit Spice Bazaar (light cinematic) ═══ */
:root{
  --ink:#2a1810;
  --ink-soft:#7a6254;
  --parchment:#faf6ef;
  --parchment-2:#f3ebe0;
  --gold:#c8860a;
  --gold-deep:#a66b08;
  --gold-light:#f5c842;
  --saffron:#ffb020;
  --terracotta:#c45c2a;
  --emerald:#1a7a4a;
  --rose:#d4546a;
  --clay:#c45c2a;
  --olive:#5a7a32;
  --card:#ffffff;
  --hair:rgba(42,24,16,.1);
  --shadow:0 22px 55px -22px rgba(42,24,16,.18);
  --radius:18px;
  --font-thuluth:'Thuluth','Amiri',serif;
  --font-body:'El Messiri',sans-serif;
  --font-ui:'Reem Kufi',sans-serif;
}
*{margin:0;padding:0;box-sizing:border-box}
[x-cloak]{display:none!important}
html{scroll-behavior:smooth}
body{
  font-family:var(--font-body);color:var(--ink);
  background:
    radial-gradient(ellipse 90% 50% at 10% -5%,rgba(255,176,32,.12),transparent 50%),
    radial-gradient(ellipse 70% 40% at 95% 10%,rgba(196,92,42,.08),transparent 45%),
    var(--parchment);
  overflow-x:hidden;min-height:100vh;
}
img{display:block;max-width:100%}
a{color:inherit;text-decoration:none}
input,textarea,select{font-family:var(--font-body);background:var(--card);color:var(--ink);border-color:var(--hair)}
button{font-family:var(--font-body);cursor:pointer}
h1,h2,h3,h4{font-family:var(--font-thuluth);font-weight:400;line-height:1.35}
.wrap{max-width:1200px;margin:0 auto;padding:0 clamp(16px,4vw,24px);position:relative;z-index:1}

/* ── Header ── */
header.top{
  position:sticky;top:0;z-index:60;
  backdrop-filter:blur(18px) saturate(1.3);
  background:rgba(250,246,239,.88);
  border-bottom:1px solid var(--hair);
  box-shadow:0 4px 24px -8px rgba(42,24,16,.08);
}
.top .wrap{display:flex;align-items:center;justify-content:space-between;min-height:70px;height:auto;padding-block:10px;gap:10px}
.brand{display:flex;align-items:center;gap:11px;font-family:var(--font-ui);font-weight:700;text-decoration:none;min-width:0;flex:1}
.brand-seal,.brand-logo{width:44px;height:44px;border-radius:50%;flex-shrink:0}
.brand-seal{
  background:linear-gradient(145deg,#fff8e8,#f5d88a);
  display:grid;place-items:center;
  color:var(--gold-deep);font-size:1.45rem;font-family:var(--font-thuluth);
  border:2px solid rgba(200,134,10,.35);
  box-shadow:0 6px 20px -6px rgba(200,134,10,.35);
}
.brand-logo{object-fit:cover;border:2px solid rgba(200,134,10,.3)}
.brand > div{min-width:0}
.brand b{
  display:block;font-size:clamp(1rem,2.8vw,1.35rem);color:var(--ink);
  font-family:var(--font-thuluth);line-height:1.2;
  overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:min(52vw,280px);
}
.brand span{display:block;font-size:.62rem;color:var(--gold);font-weight:500;margin-top:2px;font-family:var(--font-body)}
.top-actions{display:flex;gap:8px;align-items:center;flex-shrink:0}
.wa-top{
  display:flex;align-items:center;gap:7px;
  background:rgba(26,122,74,.1);color:var(--emerald);
  border:1px solid rgba(26,122,74,.22);
  padding:9px 16px;border-radius:40px;font-weight:600;font-size:.88rem;
  text-decoration:none;transition:.25s;
}
.wa-top:hover{background:rgba(26,122,74,.18);border-color:var(--emerald)}
.wa-top svg{width:17px;height:17px}
.cart-btn{
  display:flex;align-items:center;gap:7px;cursor:pointer;
  background:linear-gradient(135deg,var(--gold),var(--saffron));
  color:#fff;border:none;padding:9px 18px;border-radius:40px;
  font-weight:700;font-size:.88rem;text-decoration:none;
  transition:.25s;box-shadow:0 6px 20px -4px rgba(200,134,10,.45);
}
.cart-btn:hover{transform:translateY(-2px);box-shadow:0 10px 28px -4px rgba(200,134,10,.55)}
.cart-btn .badge{
  background:#fff;color:var(--gold-deep);
  min-width:20px;height:20px;border-radius:50%;
  display:grid;place-items:center;font-weight:700;font-size:.75rem;padding:0 4px;
}

/* ── Flash ── */
.flash{padding:14px 20px;border-radius:12px;margin:12px auto;max-width:800px;font-weight:600;text-align:center}
.flash-success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
.flash-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}

/* ── Product cards (shared) ── */
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:20px}
.card{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);overflow:hidden;
  box-shadow:var(--shadow);display:flex;flex-direction:column;transition:transform .25s,box-shadow .25s;height:100%}
.card:hover{transform:translateY(-5px);box-shadow:0 28px 60px -20px rgba(42,24,16,.22)}
.card .thumb{height:170px;overflow:hidden;background:var(--parchment-2);position:relative}
.card .thumb img{width:100%;height:100%;object-fit:cover;transition:transform .55s}
.card:hover .thumb img{transform:scale(1.08)}
.card .thumb .no-img{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:2.8rem;opacity:.35}
.badge-cat{position:absolute;top:10px;right:10px;background:rgba(42,24,16,.82);color:#fff;
  font-family:'Reem Kufi';font-size:.7rem;padding:3px 10px;border-radius:20px}
.badge-stock{position:absolute;top:10px;left:10px;font-family:'Reem Kufi';font-size:.68rem;padding:3px 9px;border-radius:20px;font-weight:600}
.badge-stock.ok{background:#d1fae5;color:#065f46}
.badge-stock.low{background:#fef3c7;color:#92400e}
.badge-stock.no{background:var(--clay);color:#fff}
.badge-sale{position:absolute;bottom:10px;right:10px;background:linear-gradient(135deg,var(--terracotta),#e86830);color:#fff;
  font-family:'Reem Kufi';font-size:.68rem;padding:4px 10px;border-radius:20px;font-weight:700}
.card .body{padding:14px 16px 16px;display:flex;flex-direction:column;gap:8px;flex:1}
.card h3{font-family:var(--font-thuluth);font-size:1.05rem;font-weight:400;line-height:1.4;color:var(--ink)}
.card .desc{font-size:.82rem;color:var(--ink-soft);line-height:1.45}
.card .price{font-family:'Reem Kufi';color:var(--gold-deep);font-weight:700;font-size:1.05rem;display:flex;align-items:baseline;gap:8px;flex-wrap:wrap}
.card .price small{color:var(--ink-soft);font-weight:400;font-size:.76rem}
.price-compare{color:var(--ink-soft);font-weight:500;font-size:.88rem;text-decoration:line-through;opacity:.7}
.unit-row{display:flex;gap:6px;flex-wrap:wrap}
.unit-opt{flex:1;min-width:58px;text-align:center;border:1px solid var(--hair);background:var(--parchment);
  border-radius:9px;padding:6px 3px;cursor:pointer;font-weight:600;font-size:.78rem;color:var(--ink-soft);transition:.15s}
.unit-opt small{display:block;font-size:.65rem;color:var(--gold-deep);font-weight:400;margin-top:2px}
.unit-opt.sel{background:var(--olive);color:#fff;border-color:var(--olive)}
.unit-opt.sel small{color:#eef0dd}
.unit-opt:disabled{opacity:.4;cursor:not-allowed}
.qty-row{display:flex;align-items:center;justify-content:space-between;gap:8px}
.unit-chip{font-size:.8rem;font-weight:600;color:var(--ink-soft)}
.stepper{display:flex;align-items:center;gap:6px}
.stepper button{width:28px;height:28px;border-radius:8px;border:1px solid var(--hair);background:var(--parchment);cursor:pointer;font-weight:700}
.stepper span{min-width:24px;text-align:center;font-weight:700}
.add-btn{background:linear-gradient(135deg,var(--ink),#4a3020);color:#fff;border:none;padding:10px;border-radius:12px;
  cursor:pointer;font-weight:600;font-size:.9rem;width:100%;transition:.22s;margin-top:auto}
.add-btn .add-btn-inner{display:flex;align-items:center;justify-content:center;gap:6px}
.add-btn:hover:not(:disabled){background:linear-gradient(135deg,var(--gold-deep),var(--gold))}
.add-btn.added{background:var(--emerald)}
.add-btn.disabled{opacity:.45;cursor:not-allowed}

/* ── Footer ── */
footer.site-footer{
  background:linear-gradient(165deg,#3d2818 0%,#2a1810 55%,#1f1208 100%);
  border-top:3px solid var(--gold);
  color:rgba(250,246,239,.75);padding:56px 0 28px;margin-top:0;
}
footer.site-footer .foot-inner{display:flex;flex-wrap:wrap;gap:36px;justify-content:space-between;margin-bottom:36px}
footer.site-footer h4{font-family:'Reem Kufi';font-size:.9rem;color:var(--gold-light);margin-bottom:14px;letter-spacing:1px}
footer.site-footer p,footer.site-footer a{font-size:.85rem;color:rgba(250,246,239,.55);line-height:1.9;display:block;transition:.2s}
footer.site-footer a:hover{color:var(--gold-light)}
.foot-copy{
  border-top:1px solid rgba(245,200,66,.15);padding-top:22px;text-align:center;
  font-size:.76rem;color:rgba(245,200,66,.4);
}

/* ── Buttons ── */
.btn-primary{
  background:linear-gradient(135deg,var(--gold),var(--saffron));
  color:#fff;border:none;padding:13px 30px;border-radius:12px;
  font-weight:700;font-size:.96rem;cursor:pointer;transition:.25s;
  display:inline-flex;align-items:center;gap:8px;
  box-shadow:0 8px 28px -6px rgba(200,134,10,.45);
}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 14px 36px -6px rgba(200,134,10,.55)}
.btn-outline{
  background:#fff;color:var(--ink);
  border:2px solid var(--hair);
  padding:11px 26px;border-radius:12px;
  font-weight:600;font-size:.92rem;cursor:pointer;transition:.25s;
  display:inline-flex;align-items:center;gap:8px;
}
.btn-outline:hover{border-color:var(--gold);color:var(--gold-deep)}

@media(max-width:600px){
  .top .wrap{min-height:60px;padding-block:8px}
  .brand b{font-size:.95rem}
  .brand-seal,.brand-logo{width:38px;height:38px}
  .wa-top{padding:7px 10px;font-size:.75rem}
  .wa-top span.wa-txt{display:none}
  .cart-btn{padding:7px 12px;font-size:.78rem}
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
        <span class="wa-txt">واتساب</span>
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
  background:#fff;color:var(--ink);padding:13px 24px;border-radius:40px;font-weight:600;font-size:.9rem;
  border:1px solid var(--hair);
  box-shadow:0 20px 50px -10px rgba(42,24,16,.2);
  transition:transform .38s cubic-bezier(.2,1.3,.4,1);
  display:flex;gap:9px;align-items:center;
}
.store-toast.show{transform:translateX(-50%) translateY(0)}
.store-toast svg{width:18px;height:18px;color:var(--emerald)}
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
