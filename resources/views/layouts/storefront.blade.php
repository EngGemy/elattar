<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="csrf-token" content="{{ csrf_token() }}">
<title>@yield('title', $shop['name'] . ' — ' . $shop['tagline'])</title>
<meta name="description" content="@yield('description', $shop['description'])">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=El+Messiri:wght@400;500;600;700&family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
<link rel="icon" href="{{ asset('images/brand-logo.png') }}" type="image/png">
<style>
/* ═══ استوديو الزعفران — تطبيق عطارة ═══ */
:root{
  --ink:#14201c;
  --ink-soft:#5a6a63;
  --parchment:#eef1ee;
  --parchment-2:#e2e8e4;
  --gold:#e0a21a;
  --gold-deep:#c48912;
  --gold-light:#f3d078;
  --gold-glow:rgba(224,162,26,.35);
  --saffron:#e0a21a;
  --copper:#b8841a;
  --emerald:#1a3a2f;
  --emerald-light:#2a5646;
  --clay:#a63d2f;
  --olive:#1a3a2f;
  --night:#0b1612;
  --card:#ffffff;
  --hair:rgba(20,32,28,.1);
  --shadow:0 16px 40px -20px rgba(11,22,18,.28);
  --radius:18px;
  --chrome-h:118px;
  --font-thuluth:'El Messiri',serif;
  --font-naskh:'IBM Plex Sans Arabic',sans-serif;
  --font-body:var(--font-naskh);
  --font-ui:'IBM Plex Sans Arabic',sans-serif;
}
*{margin:0;padding:0;box-sizing:border-box}
[x-cloak]{display:none!important}
html{
  scroll-behavior:smooth;
  -webkit-font-smoothing:antialiased;
  -moz-osx-font-smoothing:grayscale;
  text-rendering:optimizeLegibility;
  overflow-x:hidden;max-width:100%;
}
body{
  font-family:var(--font-body);font-weight:400;color:var(--ink);line-height:1.75;
  font-feature-settings:'liga' 1,'calt' 1;
  background:
    radial-gradient(ellipse 70% 40% at 100% -10%,rgba(224,162,26,.1),transparent 50%),
    radial-gradient(ellipse 50% 35% at 0% 15%,rgba(26,58,47,.08),transparent 45%),
    var(--parchment);
  overflow-x:hidden;min-height:100vh;max-width:100%;
  padding-top:var(--chrome-h);
}
img{display:block;max-width:100%}
a{color:inherit;text-decoration:none}
input,textarea,select{
  font-family:var(--font-naskh);font-weight:500;background:var(--card);color:var(--ink);border-color:var(--hair);
}
button{font-family:var(--font-naskh);font-weight:600;cursor:pointer}
h1,h2,h3,h4,.font-thuluth{
  font-family:var(--font-thuluth);font-weight:400;line-height:1.5;
  font-feature-settings:'liga' 1,'calt' 1,'mark' 1;
}
p,li,label,.font-naskh{font-family:var(--font-naskh)}
.wrap{max-width:1200px;margin:0 auto;padding:0 clamp(14px,4vw,28px);position:relative;z-index:1;min-width:0;width:100%}
@media(max-width:600px){
  .wrap{padding-inline:14px}
  .grid{gap:10px}
  .card.card-product{border-radius:16px}
}

/* ── Chrome: header + menu ثابت أثناء التمرير ── */
.site-chrome{
  position:fixed;inset-inline:0;top:0;z-index:80;width:100%;
  transition:box-shadow .3s,background .3s;
}
.site-chrome.is-scrolled{
  box-shadow:0 12px 40px -16px rgba(12,10,8,.28);
}
header.top{
  backdrop-filter:blur(20px) saturate(1.25);
  background:rgba(238,241,238,.94);
  border-bottom:1px solid transparent;
  transition:background .3s,min-height .3s,padding .3s;
}
.site-chrome.is-scrolled header.top{
  background:rgba(255,255,255,.97);
  border-bottom-color:var(--hair);
}
.top .wrap{
  display:flex;align-items:center;justify-content:space-between;
  min-height:72px;height:auto;padding-block:10px;gap:8px;min-width:0;overflow:hidden;
  transition:min-height .3s,padding .3s;
}
.site-chrome.is-scrolled .top .wrap{min-height:58px;padding-block:6px}
.brand{display:flex;align-items:center;gap:11px;font-family:var(--font-ui);font-weight:700;text-decoration:none;min-width:0;flex:1;overflow:hidden}
.brand.has-logo{gap:0;max-width:calc(100% - 130px)}
.brand-seal,.brand-logo{width:44px;height:44px;border-radius:50%;flex-shrink:0}
.brand-logo-wide{
  height:clamp(42px,9vw,56px);width:auto;max-width:min(78vw,340px);
  object-fit:contain;object-position:right center;flex-shrink:1;
  filter:drop-shadow(0 6px 18px rgba(13,10,8,.22));
  transition:height .3s;
}
.site-chrome.is-scrolled .brand-logo-wide{height:clamp(34px,7vw,44px)}
.brand-text{min-width:0}
.brand-seal{
  background:linear-gradient(145deg,#1a3a2f,#0b1612);
  display:grid;place-items:center;
  color:var(--gold-light);font-size:1.55rem;font-family:var(--font-thuluth);
  border:1px solid rgba(224,162,26,.45);
  box-shadow:0 6px 20px -6px rgba(11,22,18,.35);
}
.brand-logo{object-fit:cover;border:1px solid rgba(201,146,46,.35)}
.brand > div{min-width:0}
.brand b{
  display:block;font-size:clamp(1.05rem,2.8vw,1.45rem);color:var(--ink);
  font-family:var(--font-thuluth);line-height:1.35;
  overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:min(52vw,280px);
}
.brand span{display:block;font-size:.68rem;color:var(--copper);font-weight:500;margin-top:3px;font-family:var(--font-naskh)}
.top-actions{display:flex;gap:8px;align-items:center;flex-shrink:0}
.wa-top{
  display:flex;align-items:center;gap:7px;
  background:rgba(47,74,56,.1);color:var(--emerald);
  border:1px solid rgba(47,74,56,.22);
  padding:9px 16px;border-radius:12px;font-weight:600;font-size:.88rem;
  font-family:var(--font-ui);
  text-decoration:none;transition:.25s;
}
.wa-top:hover{background:rgba(47,74,56,.16);border-color:var(--emerald)}
.wa-top svg{width:17px;height:17px}
.cart-btn{
  display:flex;align-items:center;gap:7px;cursor:pointer;
  background:var(--emerald);color:#fff;border:1px solid transparent;
  padding:9px 18px;border-radius:14px;
  font-weight:700;font-size:.88rem;font-family:var(--font-ui);
  text-decoration:none;
  transition:.25s;box-shadow:0 8px 22px -10px rgba(26,58,47,.55);
  position:relative;
}
.cart-btn:hover{transform:translateY(-1px);background:var(--emerald-light)}
.cart-btn .badge{
  background:var(--gold);color:var(--night);
  min-width:20px;height:20px;border-radius:50%;
  display:grid;place-items:center;font-weight:700;font-size:.75rem;padding:0 4px;
}

/* قائمة التنقل الثابتة تحت الهيدر */
.site-menu{
  background:var(--night);
  border-bottom:1px solid rgba(224,162,26,.25);
  backdrop-filter:blur(12px);
}
.site-menu .wrap{
  display:flex;align-items:center;gap:4px;overflow-x:auto;scrollbar-width:none;
  min-height:46px;padding-block:0;-webkit-overflow-scrolling:touch;
}
.site-menu .wrap::-webkit-scrollbar{display:none}
.site-menu a{
  flex-shrink:0;padding:12px 14px;font-family:var(--font-ui);font-size:.82rem;font-weight:600;
  color:rgba(238,241,238,.7);letter-spacing:.02em;position:relative;transition:color .2s;
  white-space:nowrap;
}
.site-menu a:hover,.site-menu a.is-active{color:var(--gold-light)}
.site-menu a.is-active::after{
  content:'';position:absolute;inset-inline:14px;bottom:6px;height:2px;
  background:var(--gold);border-radius:2px;
}

/* ── Flash ── */
.flash{padding:14px 20px;border-radius:12px;margin:12px auto;max-width:800px;font-weight:600;text-align:center}
.flash-success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
.flash-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}

/* ── Product cards — app tiles ── */
.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
@media(min-width:720px){.grid{grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px}}
.card.card-product{
  background:var(--card);border:1px solid var(--hair);border-radius:20px;overflow:hidden;
  box-shadow:0 10px 28px -18px rgba(11,22,18,.22);
  display:flex;flex-direction:column;height:100%;
  transition:transform .35s cubic-bezier(.2,.8,.2,1),box-shadow .35s;
}
.card.card-product:active{transform:scale(.98)}
@media(hover:hover){
  .card.card-product:hover{transform:translateY(-5px);box-shadow:0 22px 44px -20px rgba(11,22,18,.3)}
  .card.card-product:hover .thumb img{transform:scale(1.04)}
}
.card .thumb{
  aspect-ratio:1/1;height:auto;width:100%;overflow:hidden;
  background:linear-gradient(165deg,#e8efeb 0%,#d4ddd8 100%);
  position:relative;display:block;
}
.card .thumb-link{cursor:pointer}
.card .thumb img{
  width:100%;height:100%;object-fit:cover;object-position:center;
  transition:transform .7s cubic-bezier(.2,.8,.2,1);
}
.card .thumb .no-img{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-family:var(--font-thuluth);font-size:2.4rem;color:var(--emerald);opacity:.45}
.badge-cat{
  position:absolute;top:10px;right:10px;background:rgba(11,22,18,.78);color:#fff;
  font-family:var(--font-ui);font-size:.65rem;padding:4px 10px;border-radius:999px;backdrop-filter:blur(6px)
}
.badge-stock{position:absolute;top:10px;left:10px;font-family:var(--font-ui);font-size:.62rem;padding:4px 8px;border-radius:999px;font-weight:600}
.badge-stock.ok{background:#d1fae5;color:#065f46}
.badge-stock.low{background:#fef3c7;color:#92400e}
.badge-stock.no{background:var(--clay);color:#fff}
.badge-sale{
  position:absolute;bottom:10px;right:10px;background:var(--gold);color:var(--night);
  font-family:var(--font-ui);font-size:.65rem;padding:4px 9px;border-radius:8px;font-weight:700
}
.card .body{padding:10px 11px 12px;display:flex;flex-direction:column;gap:6px;flex:1;min-height:0}
.card-head{min-height:0}
.card h3{font-family:var(--font-ui);font-size:clamp(.78rem,2.8vw,.9rem);font-weight:700;line-height:1.4;color:var(--ink);
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden}
.card h3 a{color:inherit;text-decoration:none}
.card .desc{display:none}
.card .price{font-family:var(--font-ui);color:var(--emerald);font-weight:700;font-size:.95rem;display:flex;align-items:baseline;gap:5px;flex-wrap:wrap}
.card .price small{color:var(--ink-soft);font-weight:500;font-size:.68rem}
.price-compare{color:var(--ink-soft);font-weight:500;font-size:.78rem;text-decoration:line-through;opacity:.65}
.variant-chips,.weight-chips{display:flex;gap:5px;overflow-x:auto;-webkit-overflow-scrolling:touch;scrollbar-width:none}
.variant-chips::-webkit-scrollbar,.weight-chips::-webkit-scrollbar{display:none}
.weight-chip{
  flex-shrink:0;padding:6px 10px;border-radius:10px;border:1px solid var(--hair);background:var(--parchment);
  font-size:.7rem;font-weight:600;color:var(--ink-soft);cursor:pointer;transition:.15s;font-family:var(--font-ui);white-space:nowrap
}
.weight-chip.sel{background:var(--emerald);color:#fff;border-color:var(--emerald)}
.weight-chip:disabled{opacity:.4;cursor:not-allowed}
.weight-chip--more{min-width:34px;text-align:center;background:transparent;font-weight:700}
.card-purchase{margin-top:auto;display:flex;flex-direction:column;gap:7px}
.weight-panel,.piece-panel{display:flex;flex-direction:column;gap:6px}
.weight-strip{display:flex;align-items:center;gap:4px;background:var(--parchment-2);border:1px solid var(--hair);border-radius:12px;padding:4px}
.weight-strip--piece{justify-content:space-between;padding-inline:8px}
.weight-step-btn{width:32px;height:32px;border:none;border-radius:10px;background:#fff;cursor:pointer;font-weight:700;font-size:1.05rem;color:var(--ink);flex-shrink:0}
.weight-input{flex:1;min-width:32px;max-width:56px;text-align:center;padding:4px 2px;border:none;background:transparent;font-size:.88rem;font-weight:700;-moz-appearance:textfield}
.weight-input::-webkit-outer-spin-button,.weight-input::-webkit-inner-spin-button{-webkit-appearance:none}
.weight-unit{font-size:.68rem;font-weight:600;color:var(--ink-soft)}
.weight-strip-total{margin-inline-start:auto;padding-inline-start:6px;border-inline-start:1px solid var(--hair);font-size:.72rem;font-weight:700;color:var(--emerald);white-space:nowrap}
.piece-qty{min-width:28px;text-align:center;font-weight:700}
.piece-panel .unit-chip{font-size:.72rem;font-weight:600;color:var(--ink-soft)}
.add-btn{
  background:var(--emerald);color:#fff;border:none;padding:11px 10px;border-radius:12px;
  cursor:pointer;font-weight:700;font-size:.82rem;width:100%;transition:.22s;font-family:var(--font-ui)
}
.add-btn .add-btn-inner{display:flex;align-items:center;justify-content:center;gap:6px}
.add-btn:hover:not(:disabled){background:var(--emerald-light)}
.add-btn.added{background:#168a4a}
.add-btn.disabled{opacity:.45;cursor:not-allowed}

@media(max-width:400px){
  .add-btn{font-size:.74rem;padding:9px 7px}
  .card .body{padding:8px 9px 10px;gap:5px}
  .weight-chip{padding:5px 8px;font-size:.65rem}
  .weight-step-btn{width:28px;height:28px}
}

/* ── Footer ── */
footer.site-footer{
  background:linear-gradient(165deg,#1a3a2f 0%,#0f241c 55%,#0b1612 100%);
  border-top:3px solid var(--gold);
  color:rgba(238,241,238,.75);padding:56px 0 28px;margin-top:0;
}
footer.site-footer .foot-inner{display:flex;flex-wrap:wrap;gap:36px;justify-content:space-between;margin-bottom:36px}
footer.site-footer h4{font-family:var(--font-ui);font-size:.9rem;color:var(--gold-light);margin-bottom:14px;letter-spacing:1px}
footer.site-footer p,footer.site-footer a{font-family:var(--font-naskh);font-size:.88rem;color:rgba(250,246,239,.55);line-height:1.9;display:block;transition:.2s}
footer.site-footer a:hover{color:var(--gold-light)}
.foot-copy{
  border-top:1px solid rgba(245,200,66,.15);padding-top:22px;text-align:center;
  font-size:.76rem;color:rgba(245,200,66,.4);
}

/* ── Buttons ── */
.btn-primary{
  background:var(--gold);color:var(--night);border:none;padding:13px 28px;border-radius:14px;
  font-family:var(--font-ui);font-weight:700;font-size:.95rem;cursor:pointer;transition:.25s;
  display:inline-flex;align-items:center;gap:8px;
  box-shadow:0 10px 28px -8px rgba(224,162,26,.55);
}
.btn-primary:hover{transform:translateY(-2px);box-shadow:0 14px 32px -6px rgba(224,162,26,.6)}
.btn-outline{
  background:transparent;color:inherit;
  border:1.5px solid currentColor;
  padding:11px 24px;border-radius:14px;
  font-family:var(--font-ui);font-weight:600;font-size:.9rem;cursor:pointer;transition:.25s;
  display:inline-flex;align-items:center;gap:8px;opacity:.92;
}
.btn-outline:hover{opacity:1;border-color:var(--gold);color:var(--gold-deep)}

@media(max-width:600px){
  :root{--chrome-h:64px}
  .site-menu{display:none}
  .top .wrap{min-height:56px;padding-block:8px;padding-inline:12px;gap:6px}
  .site-chrome.is-scrolled .top .wrap{min-height:50px}
  .brand.has-logo{max-width:calc(100% - 118px)}
  .brand-logo-wide{height:34px;max-width:100%;width:auto}
  .site-chrome.is-scrolled .brand-logo-wide{height:30px}
  .brand b{font-size:.95rem}
  .brand-seal,.brand-logo{width:36px;height:36px}
  .wa-top{padding:7px 9px;font-size:.72rem;gap:0}
  .wa-top span.wa-txt{display:none}
  .cart-btn{padding:7px 11px;font-size:.74rem;gap:5px}
  .cart-btn svg{width:15px;height:15px}
  .cart-btn .cart-txt{display:none}
  body:not(.has-dock):not(.has-product-dock){padding-bottom:calc(68px + env(safe-area-inset-bottom,0px))}
  .app-tabs{display:flex}
  footer.site-footer{
    display:block;padding:36px 0 88px;margin-top:12px;
  }
  footer.site-footer .foot-inner{gap:22px;flex-direction:column;margin-bottom:22px}
  footer.site-footer h4{margin-bottom:8px;font-size:.85rem}
  footer.site-footer p,footer.site-footer a{font-size:.82rem;line-height:1.7}
}
.app-tabs{
  display:none;position:fixed;inset-inline:0;bottom:0;z-index:85;
  background:#fff;border-top:1px solid var(--hair);
  padding:6px 8px;padding-bottom:calc(6px + env(safe-area-inset-bottom,0px));
  box-shadow:0 -10px 30px -16px rgba(11,22,18,.25);
  justify-content:space-around;align-items:stretch;gap:4px;
}
body.has-dock .app-tabs{display:none!important}
body.has-product-dock .app-tabs{display:none!important}
.app-tabs a{
  flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;
  padding:8px 4px;border-radius:12px;text-decoration:none;
  font-family:var(--font-ui);font-size:.65rem;font-weight:600;color:var(--ink-soft);
  transition:color .2s,background .2s;
}
.app-tabs a .ti{font-size:1.15rem;line-height:1;opacity:.85}
.app-tabs a.on{color:var(--emerald);background:rgba(26,58,47,.08)}
.app-tabs a.on .ti{opacity:1}
</style>
@stack('head-styles')
</head>
<body class="@yield('body-class') @if(request()->routeIs(['storefront.cart', 'storefront.checkout', 'storefront.checkout.store'])) has-dock @endif">

<div class="site-chrome" id="site-chrome">
@php
  $__cartSession = session('storefront_cart', []);
  $__cartBadge = 0;
  foreach ($__cartSession as $__line) {
      $__cartBadge += ! empty($__line['is_weighted'])
          ? 1
          : max(1, (int) round((float) ($__line['qty'] ?? 1)));
  }
@endphp
<header class="top">
  <div class="wrap">
    <a href="{{ route('storefront.home') }}" class="brand @if($shop['logo_url']) has-logo @endif">
      @if($shop['logo_url'])
        <img src="{{ $shop['logo_url'] }}" alt="{{ $shop['name'] }}" class="brand-logo-wide">
      @else
        <div class="brand-seal">ع</div>
        <div class="brand-text">
          <b>{{ $shop['name'] }}</b>
          <span>{{ $shop['tagline'] }}</span>
        </div>
      @endif
    </a>

    <nav class="top-actions" aria-label="إجراءات سريعة">
      <a href="{{ \App\Support\ShopSettings::whatsappUrl() }}" class="wa-top" target="_blank" rel="noopener">
        <svg viewBox="0 0 24 24" fill="currentColor">
          <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
          <path d="M12 0C5.373 0 0 5.373 0 12c0 2.07.527 4.02 1.448 5.724L0 24l6.437-1.426C8.1 23.467 10.009 24 12 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 21.818c-1.848 0-3.568-.497-5.05-1.364l-.362-.215-3.792.84.854-3.698-.236-.38C2.59 15.395 2.182 13.74 2.182 12 2.182 6.582 6.582 2.182 12 2.182S21.818 6.582 21.818 12 17.418 21.818 12 21.818z"/>
        </svg>
        <span class="wa-txt">واتساب</span>
      </a>
      <a href="{{ route('storefront.cart') }}" class="cart-btn" aria-label="سلة التسوق">
        <svg width="18" height="18" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
        </svg>
        <span class="cart-txt">السلة</span>
        <span class="badge" id="cart-badge" @if($__cartBadge === 0) style="display:none" @endif>{{ $__cartBadge }}</span>
      </a>
    </nav>
  </div>
</header>

<nav class="site-menu" aria-label="القائمة الرئيسية">
  <div class="wrap">
    <a href="{{ route('storefront.home') }}" @class(['is-active' => request()->routeIs('storefront.home')])>الرئيسية</a>
    <a href="{{ route('storefront.catalog') }}" @class(['is-active' => request()->routeIs('storefront.catalog') || request()->routeIs('storefront.product')])>المنتجات</a>
    <a href="{{ route('storefront.offers') }}" @class(['is-active' => request()->routeIs('storefront.offers')])>العروض</a>
    <a href="{{ route('storefront.track.lookup') }}" @class(['is-active' => request()->routeIs('storefront.track*')])>تتبّع الطلب</a>
  </div>
</nav>
</div>

@if(session('success'))
  <div class="flash flash-success wrap">{{ session('success') }}</div>
@endif
@if(session('error'))
  <div class="flash flash-error wrap">{{ session('error') }}</div>
@endif

@yield('content')

@unless(request()->routeIs(['storefront.cart', 'storefront.checkout', 'storefront.checkout.store']))
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
@endunless

<nav class="app-tabs" aria-label="التنقل السفلي">
  <a href="{{ route('storefront.home') }}" @class(['on' => request()->routeIs('storefront.home')])>
    <span class="ti">⌂</span>الرئيسية
  </a>
  <a href="{{ route('storefront.catalog') }}" @class(['on' => request()->routeIs('storefront.catalog') || request()->routeIs('storefront.product')])>
    <span class="ti">◈</span>المنتجات
  </a>
  <a href="{{ route('storefront.offers') }}" @class(['on' => request()->routeIs('storefront.offers')])>
    <span class="ti">٪</span>العروض
  </a>
  <a href="{{ route('storefront.cart') }}" @class(['on' => request()->routeIs('storefront.cart') || request()->routeIs('storefront.checkout*')])>
    <span class="ti">◎</span>السلة
  </a>
  <a href="{{ route('storefront.track.lookup') }}" @class(['on' => request()->routeIs('storefront.track*')])>
    <span class="ti">⌕</span>تتبّع
  </a>
</nav>

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
        weightGrams: 100,
        pieceQty: 1,
        loading: false,
        justAdded: false,
        showExtraWeights: false,
        weights: [
            { g: 50, label: '50 جم' },
            { g: 100, label: '100 جم' },
            { g: 250, label: '250 جم' },
            { g: 500, label: '½ كيلو' },
            { g: 1000, label: '1 كيلو' },
            { g: 25, label: '25 جم' },
            { g: 1, label: '1 جم' },
        ],

        get primaryWeights() {
            return this.weights.slice(0, 4);
        },

        get extraWeights() {
            return this.weights.slice(4);
        },

        get variant() {
            return this.product.variants.find(v => v.id === this.selectedVariantId) || this.product.variants[0];
        },

        get canAdd() {
            return this.variant && this.variant.in_stock;
        },

        init() {
            const v = this.variant;
            if (v) {
                this.pieceQty = v.step || 1;
                this.weightGrams = Math.max(v.step || 1, 100);
            }
        },

        selectVariant(id) {
            this.selectedVariantId = id;
            this.showExtraWeights = false;
            const v = this.variant;
            if (v) {
                this.pieceQty = v.step || 1;
                this.weightGrams = Math.max(v.step || 1, 100);
            }
        },

        normalizeWeight(qty) {
            const v = this.variant;
            if (!v) return qty;
            const step = v.step || 1;
            return Math.max(step, Math.round(qty / step) * step);
        },

        snapWeight() {
            const v = this.variant;
            if (!v) return;
            this.weightGrams = this.normalizeWeight(this.weightGrams || v.step || 1);
        },

        adjustWeight(delta) {
            const v = this.variant;
            if (!v) return;
            const step = v.step || 1;
            this.weightGrams = Math.max(step, this.normalizeWeight((this.weightGrams || step) + delta * step));
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
            return this.fmt(minor) + ' ج.م';
        },

        pieceLineTotal() {
            const v = this.variant;
            if (!v) return '';
            return this.fmt(Math.round(v.price_minor * this.pieceQty)) + ' ج.م';
        },

        lineTotalMinor() {
            const v = this.variant;
            if (!v) return 0;
            if (v.is_weighted) {
                const g = this.normalizeWeight(this.weightGrams || v.step || 1);
                return Math.round(v.price_minor * g / 1000);
            }
            return Math.round(v.price_minor * this.pieceQty);
        },

        addBtnLabel() {
            return 'أضف للسلة · ' + this.fmt(this.lineTotalMinor()) + ' ج.م';
        },

        stockClass() {
            return this.variant?.in_stock ? 'ok' : 'no';
        },

        stockLabel() {
            return this.variant?.in_stock ? 'متاح' : 'نفد';
        },

        orderQty() {
            const v = this.variant;
            if (!v) return 0;
            if (v.is_weighted) return this.normalizeWeight(this.weightGrams || v.step || 1);
            return this.pieceQty;
        },

        async addToCart() {
            if (!this.canAdd || this.loading) return;
            const v = this.variant;
            const qty = this.orderQty();
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
                const qtyLabel = v.is_weighted
                    ? (Math.round(data.line_qty || qty) + ' جم')
                    : String(data.line_qty || qty);
                storeToast(this.product.name + ' · في السلة: ' + qtyLabel);
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

function csrfToken() {
    return document.querySelector('meta[name="csrf-token"]')?.content || '';
}

function homeOffersFilter(promos = {}) {
    return {
        promos,
        query: '',
        category: '',
        matches(p) {
            const q = this.query.trim().toLowerCase();
            const catOk = !this.category || p.category_slug === this.category;
            const qOk = !q || p.name.toLowerCase().includes(q);
            return catOk && qOk;
        },
        promoHasVisible(slug) {
            return (this.promos[slug] || []).some((p) => this.matches(p));
        },
        setCategory(slug) {
            this.category = slug;
            this.applyFilter();
        },
        applyFilter() {
            this.$nextTick(() => this.refreshSwipers());
        },
        refreshSwipers() {
            document.querySelectorAll('.offer-prod-swiper').forEach((el) => {
                if (el._swiper) el._swiper.update();
            });
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

(function () {
    const chrome = document.getElementById('site-chrome');
    if (!chrome) return;

    const syncChromeHeight = () => {
        document.documentElement.style.setProperty('--chrome-h', chrome.offsetHeight + 'px');
    };

    const onScroll = () => {
        chrome.classList.toggle('is-scrolled', window.scrollY > 12);
    };

    syncChromeHeight();
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });
    window.addEventListener('resize', syncChromeHeight);
})();
</script>

@stack('scripts')
</body>
</html>
