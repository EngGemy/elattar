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

/* ── Flash / alerts ── */
.flash{padding:14px 18px;border-radius:14px;margin:12px auto;max-width:800px;font-weight:600;text-align:center;font-family:var(--font-ui)}
.flash-success{background:#ecfdf5;color:#065f46;border:1px solid #a7f3d0}
.flash-error{background:#fef2f2;color:#991b1b;border:1px solid #fecaca}

.store-alert{
  max-width:560px;margin:14px auto 0;padding:16px 16px 14px;
  border-radius:18px;border:1px solid rgba(166,61,47,.22);
  background:linear-gradient(160deg,#fff8f6,#fff);
  box-shadow:0 14px 36px -22px rgba(166,61,47,.35);
  font-family:var(--font-ui);
}
.store-alert-head{display:flex;gap:12px;align-items:flex-start}
.store-alert-ico{
  width:40px;height:40px;border-radius:12px;flex-shrink:0;
  display:grid;place-items:center;background:rgba(166,61,47,.1);color:var(--clay);
}
.store-alert h3{font-family:var(--font-thuluth);font-size:1.05rem;font-weight:700;color:var(--ink);margin:0 0 4px;line-height:1.35}
.store-alert p{margin:0;font-size:.86rem;color:var(--ink-soft);line-height:1.65;font-weight:500}
.store-alert ul{margin:10px 0 0;padding:0 18px 0 0;list-style:disc}
.store-alert li{font-size:.82rem;color:var(--ink);font-weight:600;margin-bottom:4px}
.store-alert-actions{margin-top:12px;display:flex;gap:8px;flex-wrap:wrap}
.store-alert-actions a{
  display:inline-flex;align-items:center;gap:6px;padding:9px 14px;border-radius:12px;
  background:var(--emerald);color:#fff;font-size:.82rem;font-weight:700;text-decoration:none;
}
.store-alert.ok{border-color:rgba(26,58,47,.18);box-shadow:0 14px 36px -22px rgba(26,58,47,.3)}
.store-alert.ok .store-alert-ico{background:rgba(26,58,47,.1);color:var(--emerald)}
@media(max-width:600px){.store-alert{margin-inline:14px;max-width:none}}


/* ── Product cards — app tiles ── */
.grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px}
@media(min-width:720px){.grid{grid-template-columns:repeat(auto-fill,minmax(200px,1fr));gap:16px}}
.card.card-product{
  background:var(--card);border:1px solid var(--hair);border-radius:16px;overflow:hidden;
  box-shadow:0 8px 24px -16px rgba(11,22,18,.2);
  display:flex;flex-direction:column;height:100%;min-width:0;
  transition:transform .35s cubic-bezier(.2,.8,.2,1),box-shadow .35s;
}
.card.card-product:active{transform:scale(.98)}
@media(hover:hover){
  .card.card-product:hover{transform:translateY(-4px);box-shadow:0 18px 36px -18px rgba(11,22,18,.28)}
  .card.card-product:hover .thumb img{transform:scale(1.03)}
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
.card .thumb .no-img{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-family:var(--font-thuluth);font-size:2.2rem;color:var(--emerald);opacity:.4}
.badge-cat{
  position:absolute;top:8px;right:8px;background:rgba(11,22,18,.78);color:#fff;
  font-family:var(--font-ui);font-size:.6rem;padding:3px 8px;border-radius:999px;backdrop-filter:blur(6px);
  max-width:70%;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
}
.badge-stock{position:absolute;top:8px;left:8px;font-family:var(--font-ui);font-size:.6rem;padding:3px 7px;border-radius:999px;font-weight:600}
.badge-stock.ok{background:#d1fae5;color:#065f46}
.badge-stock.low{background:#fef3c7;color:#92400e}
.badge-stock.no{background:var(--clay);color:#fff}
.badge-sale{
  position:absolute;bottom:8px;right:8px;background:var(--gold);color:var(--night);
  font-family:var(--font-ui);font-size:.6rem;padding:3px 8px;border-radius:8px;font-weight:700
}
.card .body{padding:10px 10px 12px;display:flex;flex-direction:column;gap:7px;flex:1;min-width:0;min-height:0;overflow:hidden}
.card-head{min-width:0}
.card h3{font-family:var(--font-ui);font-size:.84rem;font-weight:700;line-height:1.35;color:var(--ink);
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;margin:0}
.card h3 a{color:inherit;text-decoration:none}
.card .desc{display:none}

/* Mobile-first price board — kg + gram as twin tiles */
.card .price{font-family:var(--font-ui);min-width:0;display:flex;flex-direction:column;gap:4px}
.price-compare{color:var(--ink-soft);font-weight:500;font-size:.68rem;text-decoration:line-through;opacity:.65;padding-inline:2px}
.price-board{
  display:grid;grid-template-columns:1fr;gap:5px;min-width:0;
}
.price-board.is-weighted{grid-template-columns:1.15fr .95fr}
.price-tile{
  min-width:0;border-radius:12px;padding:7px 8px 6px;
  display:flex;flex-direction:column;gap:2px;
  background:linear-gradient(160deg,rgba(26,58,47,.08),rgba(26,58,47,.03));
  border:1px solid rgba(26,58,47,.12);
}
.price-tile--main{
  background:linear-gradient(155deg,#12352b 0%,#1a3a2f 55%,#0f241c 100%);
  border-color:transparent;color:#eef1ee;
  box-shadow:0 8px 18px -12px rgba(11,22,18,.45);
}
.price-tile--gram{
  background:linear-gradient(160deg,rgba(224,162,26,.14),rgba(255,255,255,.55));
  border-color:rgba(224,162,26,.28);
}
.price-tile-val{
  font-weight:800;font-size:clamp(.86rem,3.8vw,1.02rem);line-height:1.15;
  font-variant-numeric:tabular-nums;letter-spacing:-.01em;
  overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
}
.price-tile--main .price-tile-val{color:var(--gold-light)}
.price-tile--gram .price-tile-val{color:var(--emerald)}
.price-tile-meta{
  display:flex;align-items:center;gap:4px;flex-wrap:wrap;
  font-size:.58rem;font-weight:700;line-height:1.2;opacity:.88;
}
.price-tile--main .price-tile-meta{color:rgba(238,241,238,.78)}
.price-tile--gram .price-tile-meta{color:var(--ink-soft)}
.price-tile-unit{
  padding:1px 5px;border-radius:999px;font-size:.54rem;font-weight:800;
  background:rgba(255,255,255,.12);
}
.price-tile--gram .price-tile-unit{background:rgba(224,162,26,.18);color:var(--gold-deep,#9a6b12)}

.variant-chips{
  display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:5px;
}
.weight-chip{
  min-width:0;padding:7px 6px;border-radius:11px;border:1px solid var(--hair);background:var(--parchment);
  font-size:.68rem;font-weight:700;color:var(--ink-soft);cursor:pointer;transition:.15s;font-family:var(--font-ui);
  text-align:center;line-height:1.2;
}
.weight-chip.sel{background:var(--emerald);color:#fff;border-color:var(--emerald);box-shadow:0 6px 14px -8px rgba(26,58,47,.55)}
.weight-chip:disabled{opacity:.4;cursor:not-allowed}

/* أوزان سريعة — شبكة واضحة واحترافية */
.weight-presets{
  display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:6px;
}
.weight-preset{
  min-width:0;min-height:52px;padding:7px 4px;border-radius:12px;
  border:1.5px solid var(--hair);background:var(--parchment);
  display:flex;flex-direction:column;align-items:center;justify-content:center;gap:3px;
  cursor:pointer;transition:transform .15s,border-color .15s,background .15s,box-shadow .15s;
  font-family:var(--font-ui);color:var(--ink);line-height:1.15;
}
.weight-preset:active{transform:scale(.97)}
.weight-preset-label{font-size:.72rem;font-weight:800;white-space:nowrap}
.weight-preset-price{font-size:.62rem;font-weight:700;color:var(--emerald);opacity:.95}
.weight-preset.sel{
  background:linear-gradient(155deg,#1a3a2f,#12352b);color:#fff;border-color:transparent;
  box-shadow:0 8px 18px -10px rgba(11,22,18,.5);
}
.weight-preset.sel .weight-preset-price{color:var(--gold-light)}

.card-purchase{margin-top:auto;display:flex;flex-direction:column;gap:7px;min-width:0}
.weight-panel,.piece-panel{display:flex;flex-direction:column;gap:6px;min-width:0}

/* Stepper — LTR so − / + never overflow in RTL cards */
.weight-strip{
  display:flex;align-items:center;justify-content:space-between;gap:4px;
  background:var(--parchment-2);border:1px solid var(--hair);border-radius:12px;padding:4px;
  width:100%;box-sizing:border-box;min-width:0;direction:ltr;
}
.weight-step-btn{
  width:36px;height:36px;border:none;border-radius:10px;background:#fff;cursor:pointer;
  font-weight:700;font-size:1.1rem;color:var(--ink);flex-shrink:0;line-height:1;
  display:grid;place-items:center;box-shadow:0 1px 2px rgba(0,0,0,.05);
}
.weight-mid{flex:1;display:flex;align-items:center;justify-content:center;gap:3px;min-width:0}
.weight-input{
  width:48px;max-width:48%;text-align:center;padding:2px 0;border:none;background:transparent;
  font-size:.88rem;font-weight:800;-moz-appearance:textfield;font-family:var(--font-ui);min-width:0;
}
.weight-input::-webkit-outer-spin-button,.weight-input::-webkit-inner-spin-button{-webkit-appearance:none}
.weight-unit{font-size:.68rem;font-weight:700;color:var(--ink-soft);flex-shrink:0}
.piece-qty{min-width:24px;text-align:center;font-weight:800;font-size:.9rem;font-family:var(--font-ui)}
.piece-panel .unit-chip{font-size:.7rem;font-weight:700;color:var(--ink-soft)}

.add-btn{
  background:linear-gradient(160deg,#1f4a3c,#12352b);color:#fff;border:none;padding:11px 8px;border-radius:12px;
  cursor:pointer;font-weight:800;font-size:.78rem;width:100%;transition:.22s;font-family:var(--font-ui);
  min-width:0;overflow:hidden;min-height:44px;
  box-shadow:0 10px 22px -14px rgba(11,22,18,.55);
}
.add-btn .add-btn-inner{
  display:flex;align-items:center;justify-content:center;gap:5px;min-width:0;width:100%;
}
.add-btn .add-btn-txt{flex-shrink:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.add-btn .add-btn-price{
  flex-shrink:0;opacity:.95;font-size:.74rem;padding-inline-start:6px;
  border-inline-start:1px solid rgba(255,255,255,.28);margin-inline-start:2px;font-weight:800;
}
.add-btn:hover:not(:disabled){filter:brightness(1.06)}
.add-btn.added{background:linear-gradient(160deg,#1a8a4f,#147040)}
.add-btn.disabled{opacity:.45;cursor:not-allowed;box-shadow:none}

@media(max-width:360px){
  .price-board.is-weighted{grid-template-columns:1fr;gap:4px}
  .price-tile{padding:6px 8px}
  .weight-presets{grid-template-columns:repeat(2,minmax(0,1fr))}
  .weight-preset{min-height:48px}
}

@media(min-width:720px){
  .price-board.is-weighted{grid-template-columns:1.2fr 1fr;gap:8px}
  .price-tile{padding:9px 10px;border-radius:14px}
  .price-tile-val{font-size:1.05rem}
  .variant-chips{display:flex;gap:6px;overflow-x:auto;scrollbar-width:none}
  .variant-chips::-webkit-scrollbar{display:none}
  .weight-presets{grid-template-columns:repeat(5,minmax(0,1fr));gap:7px}
  .weight-preset{min-height:56px;border-radius:14px}
  .weight-preset-label{font-size:.78rem}
  .weight-preset-price{font-size:.66rem}
}

/* ── Live search suggest ── */
.store-suggest{position:relative;width:100%;z-index:50}
.store-suggest-row{display:flex;gap:8px;align-items:stretch}
.store-suggest-field{position:relative;flex:1;min-width:0}
.store-suggest-field input{
  width:100%;height:50px;border:1.5px solid rgba(26,58,47,.14);border-radius:16px;
  padding:0 46px 0 40px;font-size:16px;background:var(--card);outline:none;
  font-family:var(--font-ui);font-weight:600;
  box-shadow:0 12px 28px -16px rgba(11,22,18,.28), inset 0 1px 0 rgba(255,255,255,.7);
}
.store-suggest-field input:focus{
  border-color:var(--gold);
  box-shadow:0 0 0 3px rgba(224,162,26,.16), 0 12px 28px -16px rgba(11,22,18,.28);
}
.store-suggest-ico{
  position:absolute;right:14px;top:50%;transform:translateY(-50%);
  width:18px;height:18px;color:var(--emerald);pointer-events:none;
}
.store-suggest-clear{
  position:absolute;left:10px;top:50%;transform:translateY(-50%);
  width:28px;height:28px;border:none;border-radius:999px;background:var(--parchment-2);
  color:var(--ink-soft);font-size:1.1rem;line-height:1;cursor:pointer;
}
.store-suggest-row > button[type="submit"]{
  height:50px;padding:0 18px;border:none;border-radius:16px;
  background:linear-gradient(155deg,#1f4a3c,#12352b);color:#fff;
  font-family:var(--font-ui);font-weight:800;font-size:.88rem;
  box-shadow:0 12px 24px -12px rgba(26,58,47,.55);white-space:nowrap;flex-shrink:0;
}
.store-suggest-panel{
  position:absolute;inset-inline:0;top:calc(100% + 6px);z-index:60;
  background:var(--card);border:1px solid var(--hair);border-radius:18px;
  box-shadow:0 22px 48px -18px rgba(11,22,18,.4);
  overflow:hidden;max-height:min(68svh,420px);overflow-y:auto;
}
.store-suggest-item{
  width:100%;display:flex;align-items:center;gap:10px;padding:10px 12px;
  border:none;background:transparent;text-align:right;cursor:pointer;color:inherit;
  font-family:var(--font-ui);border-bottom:1px solid rgba(26,58,47,.06);
}
.store-suggest-item.on,.store-suggest-item:hover{background:rgba(26,58,47,.06)}
.store-suggest-thumb{
  width:42px;height:42px;border-radius:12px;overflow:hidden;flex-shrink:0;
  background:linear-gradient(165deg,#e8efeb,#d4ddd8);display:grid;place-items:center;
}
.store-suggest-thumb img{width:100%;height:100%;object-fit:cover}
.store-suggest-fallback{font-family:var(--font-thuluth);color:var(--emerald);opacity:.5;font-size:1.1rem}
.store-suggest-meta{flex:1;min-width:0;display:flex;flex-direction:column;gap:2px}
.store-suggest-meta strong{
  font-size:.88rem;font-weight:800;color:var(--ink);
  overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
}
.store-suggest-meta small{font-size:.72rem;color:var(--ink-soft);font-weight:600}
.store-suggest-go{color:var(--emerald);font-weight:800;opacity:.55;flex-shrink:0}
.store-suggest-empty{
  padding:16px 14px;text-align:center;color:var(--ink-soft);font-size:.84rem;font-family:var(--font-ui);
  display:flex;flex-direction:column;gap:10px;align-items:center;
}
.store-suggest-all,.store-suggest-footer{
  border:none;background:rgba(26,58,47,.06);color:var(--emerald);
  font-family:var(--font-ui);font-weight:800;font-size:.78rem;cursor:pointer;
  padding:10px 14px;border-radius:12px;
}
.store-suggest-footer{
  width:100%;border-radius:0;background:linear-gradient(180deg,rgba(26,58,47,.04),rgba(26,58,47,.08));
  border-top:1px solid var(--hair);
}
.store-suggest.is-compact .store-suggest-field input,
.store-suggest.is-compact .store-suggest-row > button[type="submit"]{height:46px;border-radius:14px}

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
@if(session('alert'))
  @php $__alert = session('alert'); @endphp
  <div class="store-alert {{ ($__alert['type'] ?? '') === 'ok' ? 'ok' : '' }}" role="alert">
    <div class="store-alert-head">
      <div class="store-alert-ico" aria-hidden="true">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
      </div>
      <div>
        <h3>{{ $__alert['title'] ?? 'تنبيه' }}</h3>
        <p>{{ $__alert['body'] ?? '' }}</p>
        @if(!empty($__alert['items']))
          <ul>
            @foreach($__alert['items'] as $item)
              <li>{{ $item }}</li>
            @endforeach
          </ul>
        @endif
        @if(!empty($__alert['cta_url']))
          <div class="store-alert-actions">
            <a href="{{ $__alert['cta_url'] }}">{{ $__alert['cta_label'] ?? 'متابعة' }}</a>
          </div>
        @endif
      </div>
    </div>
  </div>
@elseif(session('error'))
  <div class="store-alert" role="alert">
    <div class="store-alert-head">
      <div class="store-alert-ico" aria-hidden="true">
        <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/>
        </svg>
      </div>
      <div>
        <h3>تعذّر إكمال العملية</h3>
        <p>{{ session('error') }}</p>
      </div>
    </div>
  </div>
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
        presetWeights: [
            { g: 50, label: '50 جم' },
            { g: 100, label: '100 جم' },
            { g: 250, label: '250 جم' },
            { g: 500, label: '½ كيلو' },
            { g: 1000, label: '1 كيلو' },
        ],

        get variant() {
            return this.product.variants.find(v => v.id === this.selectedVariantId) || this.product.variants[0];
        },

        get canAdd() {
            return this.variant && this.variant.in_stock;
        },

        weightStep() {
            const step = Number(this.variant?.step);
            return step > 0 ? step : 1;
        },

        minWeight() {
            return this.weightStep();
        },

        init() {
            const v = this.variant;
            if (!v) return;
            this.pieceQty = this.weightStep();
            this.weightGrams = Math.max(this.weightStep(), 100);
            this.snapWeight();
        },

        selectVariant(id) {
            this.selectedVariantId = id;
            const v = this.variant;
            if (!v) return;
            this.pieceQty = this.weightStep();
            this.weightGrams = Math.max(this.weightStep(), 100);
            this.snapWeight();
        },

        isWeightSelected(g) {
            return Number(this.weightGrams) === Number(g);
        },

        setWeight(g) {
            const grams = Number(g);
            if (!Number.isFinite(grams) || grams <= 0) return;
            // الأوزان الجاهزة تُثبت كما هي — بدون تقريب يغيّر 250 إلى قيمة أخرى
            this.weightGrams = grams;
        },

        normalizeWeight(qty) {
            const step = this.weightStep();
            const raw = Number(qty);
            if (!Number.isFinite(raw) || raw <= 0) return step;
            return Math.max(step, Math.round(raw / step) * step);
        },

        snapWeight() {
            this.weightGrams = this.normalizeWeight(this.weightGrams);
        },

        adjustWeight(delta) {
            const step = this.weightStep();
            const current = Number(this.weightGrams) || step;
            this.weightGrams = Math.max(step, this.normalizeWeight(current + Number(delta) * step));
        },

        priceLabel() {
            const v = this.variant;
            if (!v) return '—';
            return v.price_fmt;
        },

        priceAmount() {
            const v = this.variant;
            if (!v || !v.price_fmt) return '—';
            return String(v.price_fmt).replace(/\s*ج\.م\s*$/u, '').trim();
        },

        unitSuffix() {
            const v = this.variant;
            if (!v) return '';
            return v.is_weighted ? '/ كجم' : ('/ ' + (v.label || v.unit_label || ''));
        },

        unitShort() {
            const v = this.variant;
            if (!v) return '';
            return v.is_weighted ? 'للكيلو' : ('/ ' + (v.label || v.unit_label || ''));
        },

        gramPriceLabel() {
            const v = this.variant;
            if (!v || !v.is_weighted) return '';
            const minor = Math.round(v.price_minor / 1000);
            return this.fmt(minor) + ' ج.م / جم';
        },

        gramAmount() {
            const v = this.variant;
            if (!v || !v.is_weighted) return '';
            return this.fmt(Math.round(v.price_minor / 1000));
        },

        weightPrice(g) {
            const v = this.variant;
            if (!v) return '';
            const minor = Math.round(Number(v.price_minor) * Number(g) / 1000);
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
                const g = this.orderQty();
                return Math.round(Number(v.price_minor) * g / 1000);
            }
            return Math.round(Number(v.price_minor) * Number(this.pieceQty));
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
            if (v.is_weighted) {
                const g = Number(this.weightGrams);
                // لو الرقم من الأوزان الجاهزة — أرسله كما هو
                if (this.presetWeights.some(w => Number(w.g) === g)) return g;
                return this.normalizeWeight(g || this.weightStep());
            }
            return Number(this.pieceQty) || this.weightStep();
        },

        async addToCart() {
            if (!this.canAdd || this.loading) return;
            const v = this.variant;
            // ثبّت الكمية قبل أي تحديث للواجهة
            const qty = this.orderQty();
            if (!qty || qty <= 0) {
                storeToast('اختر كمية صحيحة أولاً');
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
                const addedQty = Number(data.added_qty ?? qty);
                const totalInCart = Number(data.line_qty ?? addedQty);
                const addedLabel = v.is_weighted ? (Math.round(addedQty) + ' جم') : String(addedQty);
                const totalLabel = v.is_weighted ? (Math.round(totalInCart) + ' جم') : String(totalInCart);
                storeToast(
                    Math.round(totalInCart) !== Math.round(addedQty)
                        ? (this.product.name + ' · أُضيف ' + addedLabel + ' (في السلة: ' + totalLabel + ')')
                        : (this.product.name + ' · ' + addedLabel + ' في السلة')
                );
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

function storeSuggest(opts = {}) {
    return {
        q: opts.initial || '',
        endpoint: opts.endpoint || '/products/suggest',
        catalogUrl: opts.catalogUrl || '/products',
        open: false,
        loading: false,
        items: [],
        highlight: -1,
        reqId: 0,

        onFocus() {
            if (this.items.length || (this.q.trim().length && this.loading)) {
                this.open = true;
            } else if (this.q.trim().length) {
                this.fetchSuggest();
            }
        },

        close() {
            this.open = false;
            this.highlight = -1;
        },

        clear() {
            this.q = '';
            this.items = [];
            this.close();
        },

        async fetchSuggest() {
            const term = this.q.trim();
            if (term.length < 1) {
                this.items = [];
                this.open = false;
                this.loading = false;
                return;
            }

            const id = ++this.reqId;
            this.loading = true;
            this.open = true;

            try {
                const url = this.endpoint + (this.endpoint.includes('?') ? '&' : '?') + 'q=' + encodeURIComponent(term);
                const res = await fetch(url, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
                });
                const data = await res.json();
                if (id !== this.reqId) return;
                this.items = Array.isArray(data.items) ? data.items : [];
                this.highlight = this.items.length ? 0 : -1;
            } catch (e) {
                if (id !== this.reqId) return;
                this.items = [];
            } finally {
                if (id === this.reqId) this.loading = false;
            }
        },

        move(delta) {
            if (!this.items.length) return;
            this.open = true;
            const len = this.items.length;
            this.highlight = (this.highlight + delta + len) % len;
        },

        chooseOrSubmit() {
            if (this.open && this.highlight >= 0 && this.items[this.highlight]) {
                this.go(this.items[this.highlight]);
                return;
            }
            this.submit();
        },

        go(item) {
            if (!item?.url) return;
            window.location.href = item.url;
        },

        submit() {
            const term = this.q.trim();
            const url = new URL(this.catalogUrl, window.location.origin);
            if (term) url.searchParams.set('q', term);
            // احتفظ بالتصنيف إن وُجد في النموذج الأب
            const form = this.$el.closest('form');
            const cat = form?.querySelector('input[name="category"]')?.value;
            const sort = form?.querySelector('input[name="sort"]')?.value;
            if (cat) url.searchParams.set('category', cat);
            if (sort) url.searchParams.set('sort', sort);
            window.location.href = url.toString();
        },
    };
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
