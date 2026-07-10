@extends('layouts.storefront')

@section('title', $shop['name'] . ' — ' . $shop['tagline'])

@push('head-styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<style>
/* ══ SUNLIT CINEMATIC HOME ══ */
.home-hero{
  position:relative;width:100%;max-width:100vw;overflow:hidden;
  margin:0;padding:0;
}
.hero-swiper{height:min(88vh,720px);width:100%;max-width:100%}
.hero-swiper .swiper-slide{display:flex;align-items:stretch;position:relative;overflow:hidden}
.hero-slide{
  flex:1;display:grid;grid-template-columns:minmax(0,1.1fr) minmax(0,.9fr);align-items:center;
  gap:clamp(20px,4vw,40px);
  padding:clamp(32px,5vw,48px) clamp(20px,5vw,60px) clamp(48px,6vw,56px);
  position:relative;min-height:100%;min-width:0;width:100%;
}
.hero-slide.theme-saffron{background:linear-gradient(125deg,#fff9ee 0%,#ffe8b0 38%,#ffc860 100%)}
.hero-slide.theme-ember{background:linear-gradient(125deg,#fff5ee 0%,#ffd0b0 40%,#ff9860 100%)}
.hero-slide.theme-olive{background:linear-gradient(125deg,#f5faf0 0%,#d8eeb8 42%,#8ec060 100%)}
.hero-slide.theme-rose{background:linear-gradient(125deg,#fff5f8 0%,#ffc8d8 45%,#f08098 100%)}
.hero-slide::before{
  content:'';position:absolute;inset:0;pointer-events:none;opacity:.55;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='200' height='200'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.75' numOctaves='3'/%3E%3C/filter%3E%3Crect width='200' height='200' filter='url(%23n)' opacity='.08'/%3E%3C/svg%3E");
}
.hero-slide::after{
  content:'';position:absolute;width:min(520px,90vw);height:min(520px,90vw);border-radius:50%;
  background:radial-gradient(circle,rgba(255,255,255,.55),transparent 68%);
  top:-120px;inset-inline-start:-80px;pointer-events:none;
}
.hero-slide > *{min-width:0}
.hero-copy{
  position:relative;z-index:2;min-width:0;max-width:100%;
  overflow-wrap:break-word;word-break:break-word;
  padding-inline:clamp(4px,2vw,12px);
  animation:heroIn .8s cubic-bezier(.2,.8,.2,1) both;
}
.hero-eyebrow{
  display:inline-flex;align-items:center;gap:8px;padding:7px 16px;border-radius:30px;
  background:rgba(255,255,255,.55);border:1px solid rgba(42,24,16,.08);
  font-family:'Reem Kufi';font-size:.74rem;letter-spacing:1.5px;color:var(--gold-deep);
  margin-bottom:20px;backdrop-filter:blur(8px);
}
.hero-eyebrow::before{content:'';width:7px;height:7px;border-radius:50%;background:var(--saffron);box-shadow:0 0 10px var(--saffron)}
.hero-h1{
  font-family:var(--font-thuluth);font-size:clamp(1.85rem,4.5vw,3.2rem);font-weight:400;
  color:var(--ink);line-height:1.35;margin-bottom:16px;
  text-shadow:0 2px 40px rgba(255,255,255,.4);
  overflow-wrap:break-word;word-break:break-word;max-width:100%;
}
.hero-h1 em{font-style:normal;color:var(--terracotta)}
.hero-sub{font-size:clamp(.92rem,2.2vw,1.05rem);line-height:1.85;color:var(--ink-soft);max-width:min(480px,100%);margin-bottom:28px}
.hero-cta{display:flex;flex-wrap:wrap;gap:12px}
.hero-badge{
  display:inline-block;margin-bottom:14px;padding:6px 14px;border-radius:20px;
  background:linear-gradient(135deg,var(--terracotta),#e86830);color:#fff;
  font-family:'Reem Kufi';font-weight:700;font-size:.8rem;
  box-shadow:0 6px 20px -4px rgba(196,92,42,.45);
}
.hero-visual{position:relative;z-index:2;min-width:0;display:flex;align-items:center;justify-content:center;animation:heroIn .9s .1s cubic-bezier(.2,.8,.2,1) both}
.hero-frame{
  position:relative;width:min(100%,380px);aspect-ratio:1;border-radius:28px;overflow:hidden;
  box-shadow:0 40px 80px -20px rgba(42,24,16,.28),0 0 0 6px rgba(255,255,255,.5);
  transform:rotate(-2deg);transition:transform .5s;
}
.hero-swiper .swiper-slide-active .hero-frame{transform:rotate(0deg) scale(1.02)}
.hero-frame img{width:100%;height:100%;object-fit:cover}
.hero-frame-placeholder{
  width:100%;height:100%;display:flex;align-items:center;justify-content:center;
  background:linear-gradient(145deg,rgba(255,255,255,.6),rgba(255,220,140,.4));
  font-family:var(--font-thuluth);font-size:clamp(3.5rem,12vw,6rem);color:var(--gold);
}
.hero-frame-glow{
  position:absolute;inset:-20%;background:radial-gradient(circle,rgba(255,176,32,.25),transparent 60%);
  pointer-events:none;z-index:-1;
}
.hero-swiper .swiper-pagination{bottom:28px!important}
.hero-swiper .swiper-pagination-bullet{
  width:10px;height:10px;background:rgba(42,24,16,.2);opacity:1;transition:.25s;
}
.hero-swiper .swiper-pagination-bullet-active{
  width:28px;border-radius:6px;background:linear-gradient(90deg,var(--gold),var(--saffron));
}
.hero-swiper .swiper-button-prev,.hero-swiper .swiper-button-next{
  color:var(--ink);background:rgba(255,255,255,.85);width:48px;height:48px;border-radius:50%;
  box-shadow:0 8px 24px -6px rgba(42,24,16,.15);backdrop-filter:blur(8px);
  top:50%;transform:translateY(-50%);
}
.hero-swiper .swiper-button-prev{right:auto;left:16px}
.hero-swiper .swiper-button-next{left:auto;right:16px}
.hero-swiper .swiper-button-prev:after,.hero-swiper .swiper-button-next:after{font-size:1.1rem;font-weight:900}
@keyframes heroIn{from{opacity:0;transform:translateY(28px)}to{opacity:1;transform:none}}

/* ── Ticker ── */
.home-ticker{
  background:linear-gradient(90deg,var(--gold-deep),var(--saffron),var(--terracotta));
  padding:14px 0;overflow:hidden;position:relative;
}
.home-ticker-track{display:flex;white-space:nowrap;width:max-content;animation:ticker 28s linear infinite}
.home-ticker span{
  display:inline-flex;align-items:center;gap:10px;padding:0 32px;
  font-family:'Reem Kufi';font-size:.82rem;font-weight:600;color:#fff;
}
.home-ticker .dot{opacity:.5}
@keyframes ticker{to{transform:translateX(50%)}}

/* ── Quick pills ── */
.home-pills{display:grid;grid-template-columns:repeat(4,1fr);gap:14px;padding:36px 0}
.home-pill{
  display:flex;align-items:center;gap:14px;padding:18px 20px;border-radius:18px;
  background:#fff;border:1px solid var(--hair);box-shadow:var(--shadow);
  transition:all .3s;text-decoration:none;
}
.home-pill:hover{transform:translateY(-4px);border-color:rgba(200,134,10,.35);box-shadow:0 24px 50px -18px rgba(200,134,10,.25)}
.home-pill-ico{
  width:52px;height:52px;border-radius:14px;flex-shrink:0;
  display:grid;place-items:center;font-size:1.5rem;
  background:linear-gradient(135deg,#fff8e8,#ffe4a8);
}
.home-pill b{display:block;font-family:'Reem Kufi';font-size:.92rem;color:var(--ink)}
.home-pill small{font-size:.72rem;color:var(--ink-soft)}

/* ── Section headers ── */
.sec-head{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;margin-bottom:28px;flex-wrap:wrap}
.sec-over{font-family:'Reem Kufi';font-size:.72rem;letter-spacing:2.5px;color:var(--gold);margin-bottom:6px}
.sec-title{font-family:var(--font-thuluth);font-size:clamp(1.6rem,3.2vw,2.4rem);color:var(--ink);line-height:1.3;font-weight:400}
.sec-sub{color:var(--ink-soft);font-size:.9rem;margin-top:6px}
.sec-link{font-family:'Reem Kufi';font-size:.85rem;font-weight:600;color:var(--gold-deep);white-space:nowrap}

/* ── Category swiper ── */
.cat-swiper{padding-bottom:8px}
.cat-swiper .swiper-slide{width:auto}
.cat-card{
  display:flex;flex-direction:column;align-items:center;gap:10px;
  width:130px;padding:22px 16px 18px;border-radius:22px;
  background:#fff;border:1px solid var(--hair);box-shadow:var(--shadow);
  text-decoration:none;transition:.28s;
}
.cat-card:hover{transform:translateY(-6px) scale(1.03);border-color:var(--gold);background:linear-gradient(180deg,#fff,#fff8ee)}
.cat-card.star{width:160px;background:linear-gradient(145deg,#fff8e8,#ffe4a8);border-color:rgba(200,134,10,.3)}
.cat-card .ico{font-size:2rem;line-height:1}
.cat-card b{font-family:'Reem Kufi';font-size:.85rem;text-align:center;color:var(--ink)}

/* ── Story cinematic band ── */
.home-story{
  margin:48px 0;padding:clamp(48px,6vw,72px) clamp(24px,5vw,56px);border-radius:28px;position:relative;overflow:hidden;
  background:linear-gradient(135deg,#fff9f0 0%,#ffe8c8 50%,#ffd898 100%);
  box-shadow:inset 0 1px 0 rgba(255,255,255,.8),var(--shadow);
}
.home-story::before{
  content:'عطارة';position:absolute;font-family:var(--font-thuluth);font-size:clamp(5rem,14vw,11rem);
  color:rgba(200,134,10,.06);top:50%;left:50%;transform:translate(-50%,-50%);
  white-space:nowrap;pointer-events:none;max-width:100%;overflow:hidden;
}
.home-story-grid{display:grid;grid-template-columns:minmax(0,1fr) auto minmax(0,1fr);gap:clamp(24px,4vw,48px);align-items:center;position:relative;z-index:1}
.home-story-grid > *{min-width:0}
.home-story h2{font-family:var(--font-thuluth);font-size:clamp(1.6rem,3.2vw,2.5rem);line-height:1.45;margin-bottom:16px;color:var(--ink);font-weight:400;overflow-wrap:break-word}
.home-story p{color:var(--ink-soft);line-height:1.9;font-size:1rem}
.home-chips{display:flex;flex-wrap:wrap;gap:10px;margin-top:22px}
.home-chip{
  padding:8px 16px;border-radius:30px;background:#fff;border:1px solid var(--hair);
  font-family:'Reem Kufi';font-size:.78rem;font-weight:600;color:var(--ink-soft);
  box-shadow:0 4px 12px -4px rgba(42,24,16,.08);
}
.home-story-divider{width:2px;height:180px;background:linear-gradient(to bottom,transparent,var(--gold),transparent);border-radius:2px}
.home-brand-center{text-align:center}
.home-brand-center .name{font-family:var(--font-thuluth);font-size:clamp(1.7rem,3.8vw,2.8rem);line-height:1.45;color:var(--gold-deep);margin-bottom:10px;font-weight:400;overflow-wrap:break-word}
.home-brand-center .addr{color:var(--ink-soft);font-size:.88rem;margin-bottom:14px}
.home-brand-center .phone{
  display:inline-flex;align-items:center;gap:8px;color:var(--emerald);
  font-weight:700;font-size:1.05rem;border-bottom:2px solid rgba(26,122,74,.25);padding-bottom:2px;
}

/* ── Promo swiper ── */
.promo-swiper{border-radius:24px;overflow:hidden;margin-bottom:12px}
.promo-slide{
  min-height:200px;padding:36px 40px;display:flex;align-items:center;justify-content:space-between;gap:24px;
  background-size:cover;background-position:center;position:relative;
}
.promo-slide::after{content:'';position:absolute;inset:0;background:linear-gradient(105deg,rgba(42,24,16,.82) 0%,rgba(42,24,16,.45) 55%,transparent 100%)}
.promo-slide-body{position:relative;z-index:1;color:#fff;max-width:520px}
.promo-slide-body h3{font-family:var(--font-thuluth);font-size:clamp(1.4rem,3vw,1.8rem);margin-bottom:8px;font-weight:400}
.promo-slide-body p{opacity:.88;font-size:.92rem;line-height:1.7;margin-bottom:16px}
.promo-disc{
  display:inline-block;background:linear-gradient(135deg,var(--saffron),var(--terracotta));
  padding:8px 18px;border-radius:20px;font-family:'Reem Kufi';font-weight:700;font-size:.85rem;
  box-shadow:0 8px 24px -6px rgba(0,0,0,.3);
}

/* ── Product swipers ── */
.prod-swiper-wrap{position:relative;padding:0 4px 40px}
.prod-swiper .swiper-slide{height:auto;width:270px}
.prod-swiper .swiper-button-prev,.prod-swiper .swiper-button-next{
  top:auto;bottom:0;color:var(--ink);background:#fff;width:42px;height:42px;border-radius:50%;
  border:1px solid var(--hair);box-shadow:var(--shadow);
}
.prod-swiper .swiper-button-prev{right:52px;left:auto}
.prod-swiper .swiper-button-next{right:0;left:auto}
.prod-swiper .swiper-button-prev:after,.prod-swiper .swiper-button-next:after{font-size:.9rem;font-weight:900}

/* ── Stats strip ── */
.home-stats{
  display:grid;grid-template-columns:repeat(3,1fr);gap:clamp(10px,2vw,16px);margin-top:-28px;
  position:relative;z-index:5;padding:0 clamp(16px,4vw,24px);max-width:900px;margin-left:auto;margin-right:auto;
}
.home-stat{
  text-align:center;padding:22px 16px;border-radius:18px;
  background:#fff;border:1px solid var(--hair);box-shadow:var(--shadow);
}
.home-stat .n{font-family:var(--font-thuluth);font-size:clamp(1.6rem,4vw,2rem);font-weight:400;
  background:linear-gradient(135deg,var(--gold-deep),var(--saffron));
  -webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;
}
.home-stat .l{font-size:.75rem;color:var(--ink-soft);margin-top:4px;font-family:'Reem Kufi'}

/* ── Offer cards grid inside section ── */
.offer-block{
  background:#fff;border-radius:24px;border:1px solid var(--hair);
  box-shadow:var(--shadow);overflow:hidden;margin-bottom:28px;
}
.offer-block-head{padding:24px 28px;border-bottom:1px solid var(--hair);display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap}
.offer-block-head h3{font-family:var(--font-thuluth);font-size:clamp(1.25rem,2.5vw,1.5rem);font-weight:400}
.offer-block-body{padding:24px 28px 28px}

/* ── Offers toolbar + mobile swiper ── */
.offers-section{margin:56px 0 48px}
.offers-toolbar{
  background:var(--card);border:1px solid var(--hair);border-radius:18px;
  padding:16px;margin-bottom:20px;box-shadow:var(--shadow);
}
.offers-search{position:relative;margin-bottom:12px}
.offers-search input{
  width:100%;background:var(--parchment);border:1.5px solid var(--hair);border-radius:40px;
  padding:11px 44px 11px 16px;font-size:.95rem;color:var(--ink);outline:none;transition:.18s;
}
.offers-search input:focus{border-color:var(--gold)}
.offers-search svg{
  position:absolute;right:14px;top:50%;transform:translateY(-50%);
  width:18px;height:18px;color:var(--ink-soft);pointer-events:none;
}
.offers-filters{display:flex;gap:8px;flex-wrap:wrap;align-items:center}
.offers-chip{
  background:var(--parchment-2);border:1px solid var(--hair);color:var(--ink-soft);
  padding:6px 14px;border-radius:30px;cursor:pointer;font-weight:600;font-size:.82rem;
  transition:.15s;white-space:nowrap;font-family:var(--font-ui);
}
.offers-chip:hover{border-color:var(--gold)}
.offers-chip.active{background:var(--ink);color:var(--parchment);border-color:var(--ink)}
.offers-empty{
  text-align:center;padding:28px 16px;color:var(--ink-soft);font-size:.9rem;
  border:1px dashed var(--hair);border-radius:14px;margin-top:8px;
}
.offer-prod-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:20px}
.offer-prod-swiper-wrap{display:none;position:relative;padding:0 4px 8px}
.offer-prod-swiper .swiper-slide{width:min(78vw,260px);height:auto}
.offer-prod-swiper .card .thumb{height:140px}
.offer-prod-item[hidden]{display:none!important}

@media(max-width:768px){
  .offers-section{margin:40px 0 32px}
  .sec-head{margin-bottom:18px}
  .offer-block{margin-bottom:20px;border-radius:18px}
  .offer-block-head{padding:16px 18px}
  .offer-block-body{padding:0 0 18px}
  .offer-prod-grid{display:none}
  .offer-prod-swiper-wrap{display:block;padding:0 16px}
  .offers-toolbar{padding:14px;margin-bottom:16px}
}

@media(max-width:1100px){
  .hero-slide{
    grid-template-columns:1fr;text-align:center;
    padding:clamp(28px,6vw,40px) clamp(20px,5vw,32px) 72px;
  }
  .hero-sub{margin-inline:auto}
  .hero-cta{justify-content:center}
  .hero-visual{order:-1}
  .hero-frame{width:min(100%,240px);transform:none!important;margin-inline:auto}
  .hero-swiper .swiper-button-prev,.hero-swiper .swiper-button-next{display:none}
}
@media(max-width:900px){
  .home-pills{grid-template-columns:repeat(2,1fr)}
  .home-story-grid{grid-template-columns:1fr;gap:32px;padding-inline:clamp(16px,4vw,24px)}
  .home-story-divider{display:none}
  .home-brand-center{text-align:center}
  .home-stats{margin-top:20px}
  .promo-slide{flex-direction:column;text-align:center;padding:28px 24px;min-height:auto}
  .promo-slide-body{max-width:100%}
}
@media(max-width:540px){
  .home-pills{grid-template-columns:1fr}
  .hero-swiper{height:auto;min-height:min(92vh,680px)}
  .hero-h1{font-size:clamp(1.65rem,7vw,2.1rem)}
  .home-stats{grid-template-columns:repeat(3,1fr);gap:8px}
  .home-stat{padding:16px 8px}
  .home-stat .l{font-size:.68rem}
  .home-stat .n{font-size:1.45rem}
}
</style>
@endpush

@section('content')

{{-- ══ HERO SWIPER ══ --}}
<section class="home-hero">
  <div class="swiper hero-swiper" id="hero-swiper">
    <div class="swiper-wrapper">
      @foreach($heroSlides as $slide)
        <div class="swiper-slide">
          <div class="hero-slide theme-{{ $slide['theme'] }}">
            <div class="hero-copy">
              @if($slide['badge'])
                <span class="hero-badge">{{ $slide['badge'] }}</span>
              @endif
              <div class="hero-eyebrow">{{ $slide['eyebrow'] }}</div>
              <h1 class="hero-h1">{{ $slide['title'] }}</h1>
              <p class="hero-sub">{{ $slide['subtitle'] }}</p>
              <div class="hero-cta">
                <a href="{{ $slide['cta'] }}" class="btn-primary">{{ $slide['cta_label'] }}</a>
                <a href="{{ route('storefront.offers') }}" class="btn-outline">العروض</a>
              </div>
            </div>
            <div class="hero-visual">
              <div class="hero-frame-glow"></div>
              <div class="hero-frame">
                @if($slide['image'])
                  <img src="{{ $slide['image'] }}" alt="" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
                @else
                  <div class="hero-frame-placeholder">🌶</div>
                @endif
              </div>
            </div>
          </div>
        </div>
      @endforeach
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
  </div>

  <div class="home-stats wrap">
    <div class="home-stat">
      <div class="n">{{ $categories->count() ?: '٨' }}+</div>
      <div class="l">تصنيف</div>
    </div>
    <div class="home-stat">
      <div class="n">{{ count($featured) ?: '٥٠' }}+</div>
      <div class="l">منتج طازج</div>
    </div>
    <div class="home-stat">
      <div class="n">{{ count($shop['delivery_cities']) }}</div>
      <div class="l">مدن توصيل</div>
    </div>
  </div>
</section>

{{-- ══ TICKER ══ --}}
<div class="home-ticker" aria-hidden="true">
  <div class="home-ticker-track">
    @for($i = 0; $i < 2; $i++)
      <span>بهارات طازجة يوميًا <span class="dot">✦</span></span>
      <span>توصيل {{ implode(' و', $shop['delivery_cities']) }} <span class="dot">✦</span></span>
      <span>دفع كاش · إنستاباي · فودافون كاش <span class="dot">✦</span></span>
      <span>{{ $shop['name'] }} — جودة مضمونة <span class="dot">✦</span></span>
      <span>دعم واتساب على مدار اليوم <span class="dot">✦</span></span>
    @endfor
  </div>
</div>

<div class="wrap">

  {{-- ══ QUICK NAV ══ --}}
  <div class="home-pills">
    <a href="{{ route('storefront.catalog') }}" class="home-pill">
      <div class="home-pill-ico">🛒</div>
      <div><b>المنتجات</b><small>تصفّح الكتالوج</small></div>
    </a>
    <a href="{{ route('storefront.offers') }}" class="home-pill">
      <div class="home-pill-ico">🏷</div>
      <div><b>العروض</b><small>خصومات حصرية</small></div>
    </a>
    <a href="{{ route('storefront.track.lookup') }}" class="home-pill">
      <div class="home-pill-ico">📦</div>
      <div><b>تتبّع طلبك</b><small>رقم + هاتف</small></div>
    </a>
    <a href="{{ \App\Support\ShopSettings::whatsappUrl('مرحبًا') }}" class="home-pill" target="_blank" rel="noopener">
      <div class="home-pill-ico">💬</div>
      <div><b>واتساب</b><small>رد سريع</small></div>
    </a>
  </div>

  {{-- ══ CATEGORIES SWIPER ══ --}}
  @if($categories->count())
  <section style="margin-bottom:56px">
    <div class="sec-head">
      <div>
        <div class="sec-over">تسوّق حسب التصنيف</div>
        <h2 class="sec-title">ماذا تبحث اليوم؟</h2>
      </div>
      <a href="{{ route('storefront.catalog') }}" class="sec-link">كل المنتجات ←</a>
    </div>
    <div class="swiper cat-swiper" id="cat-swiper">
      <div class="swiper-wrapper">
        <div class="swiper-slide">
          <a href="{{ route('storefront.catalog') }}" class="cat-card star">
            <span class="ico">✦</span>
            <b>كل المنتجات</b>
          </a>
        </div>
        @foreach($categories as $cat)
          <div class="swiper-slide">
            <a href="{{ route('storefront.catalog', ['category' => $cat->slug]) }}" class="cat-card">
              <span class="ico">{{ $cat->icon && !str_starts_with($cat->icon, 'heroicon') ? $cat->icon : '🏷' }}</span>
              <b>{{ $cat->name }}</b>
            </a>
          </div>
        @endforeach
      </div>
    </div>
  </section>
  @endif

  {{-- ══ STORY ══ --}}
  <section class="home-story">
    <div class="home-story-grid">
      <div>
        <h2>تراث عطارة<br>منذ أجيال</h2>
        <p>{{ $shop['description'] }} نختار بعناية كل حبة ونوصّل طلبك طازجًا إلى {{ implode(' و', $shop['delivery_cities']) }}.</p>
        <div class="home-chips">
          <span class="home-chip">🌿 بهارات أصلية</span>
          <span class="home-chip">⚖️ بيع بالوزن</span>
          <span class="home-chip">🚚 توصيل سريع</span>
          <span class="home-chip">💳 دفع مرن</span>
        </div>
      </div>
      <div class="home-story-divider"></div>
      <div class="home-brand-center">
        <div class="name">{{ $shop['name'] }}</div>
        <div class="addr">{{ $shop['address'] }}</div>
        @if($shop['phone'])
          <a href="tel:{{ $shop['phone'] }}" class="phone">📞 {{ $shop['phone'] }}</a>
        @endif
      </div>
    </div>
  </section>

  {{-- ══ PROMO SWIPER ══ --}}
  @if(count($homePromotions))
  @php
    $offerProductCategories = collect($homePromotions)
        ->flatMap(fn ($promo) => $promo['products'])
        ->filter(fn ($p) => ! empty($p['category_slug']))
        ->unique('category_slug')
        ->sortBy('category')
        ->values();
    $promoProductsMap = collect($homePromotions)->mapWithKeys(function ($promo) {
        return [$promo['slug'] => collect($promo['products'])->map(fn ($p) => [
            'name' => $p['name'],
            'category_slug' => $p['category_slug'] ?? '',
        ])->values()->all()];
    })->all();
  @endphp
  <section class="offers-section" x-data="homeOffersFilter(@js($promoProductsMap))">
    <div class="sec-head">
      <div>
        <div class="sec-over">عروض {{ $shop['name'] }}</div>
        <h2 class="sec-title">خصومات حصرية</h2>
        <p class="sec-sub">عروض محدودة — لا تفوّت الفرصة</p>
      </div>
      <a href="{{ route('storefront.offers') }}" class="sec-link">كل العروض ←</a>
    </div>

    <div class="offers-toolbar">
      <div class="offers-search">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <input type="search" x-model="query" @input.debounce.200ms="applyFilter()"
               placeholder="ابحث في منتجات العروض…" autocomplete="off">
      </div>
      <div class="offers-filters">
        <button type="button" class="offers-chip" :class="category === '' && 'active'" @click="setCategory('')">الكل</button>
        @foreach($offerProductCategories as $oc)
          <button type="button" class="offers-chip"
                  :class="category === '{{ $oc['category_slug'] }}' && 'active'"
                  @click="setCategory('{{ $oc['category_slug'] }}')">
            {{ $oc['category'] }}
          </button>
        @endforeach
      </div>
    </div>

    @if(count($homePromotions) > 1)
      <div class="swiper promo-swiper" id="promo-swiper">
        <div class="swiper-wrapper">
          @foreach($homePromotions as $promo)
            <div class="swiper-slide">
              <div class="promo-slide" @if($promo['banner']) style="background-image:url('{{ $promo['banner'] }}')" @else style="background:linear-gradient(135deg,#c8860a,#e86830)" @endif>
                <div class="promo-slide-body">
                  <span class="promo-disc">{{ $promo['discount_label'] }}</span>
                  <h3 style="margin-top:14px">{{ $promo['name'] }}</h3>
                  @if($promo['description'])<p>{{ $promo['description'] }}</p>@endif
                  @if($promo['show_countdown'] && $promo['days_remaining'] !== null)
                    <p style="font-size:.8rem;opacity:.75">⏳ {{ $promo['days_remaining'] }} أيام متبقية</p>
                  @endif
                  <a href="{{ route('storefront.offers') }}" class="btn-primary" style="margin-top:8px">تسوّق العرض</a>
                </div>
              </div>
            </div>
          @endforeach
        </div>
        <div class="swiper-pagination"></div>
      </div>
    @endif

    @foreach($homePromotions as $promo)
      @if(count($promo['products']))
        <div class="offer-block" x-show="promoHasVisible('{{ $promo['slug'] }}')" x-cloak>
          <div class="offer-block-head">
            <div>
              <h3>{{ $promo['name'] }}</h3>
              <p class="sec-sub">{{ $promo['discount_label'] }}</p>
            </div>
          </div>
          <div class="offer-block-body">
            <div class="offer-prod-grid">
              @foreach($promo['products'] as $p)
                <div class="offer-prod-item"
                     data-promo="{{ $promo['slug'] }}"
                     data-name="{{ $p['name'] }}"
                     data-category="{{ $p['category_slug'] ?? '' }}"
                     :hidden="!matches(@js(['name' => $p['name'], 'category_slug' => $p['category_slug'] ?? '']))">
                  @include('storefront.partials.product-card', ['p' => $p])
                </div>
              @endforeach
            </div>
            <div class="offer-prod-swiper-wrap">
              <div class="swiper offer-prod-swiper" data-promo="{{ $promo['slug'] }}">
                <div class="swiper-wrapper">
                  @foreach($promo['products'] as $p)
                    <div class="swiper-slide offer-prod-item"
                         data-promo="{{ $promo['slug'] }}"
                         data-name="{{ $p['name'] }}"
                         data-category="{{ $p['category_slug'] ?? '' }}"
                         :hidden="!matches(@js(['name' => $p['name'], 'category_slug' => $p['category_slug'] ?? '']))">
                      @include('storefront.partials.product-card', ['p' => $p])
                    </div>
                  @endforeach
                </div>
              </div>
            </div>
            <div class="offers-empty" x-show="!promoHasVisible('{{ $promo['slug'] }}')">
              لا توجد منتجات مطابقة في هذا العرض.
            </div>
          </div>
        </div>
      @endif
    @endforeach
  </section>
  @endif

  {{-- ══ FEATURED SWIPER ══ --}}
  @if(count($featured))
  <section style="margin-bottom:64px">
    <div class="sec-head">
      <div>
        <div class="sec-over">اختياراتنا</div>
        <h2 class="sec-title">منتجات مميّزة</h2>
        <p class="sec-sub">اسحب لاستكشاف الأكثر طلبًا</p>
      </div>
      <a href="{{ route('storefront.catalog') }}" class="sec-link">عرض الكل ←</a>
    </div>
    <div class="prod-swiper-wrap">
      <div class="swiper prod-swiper" id="feat-swiper">
        <div class="swiper-wrapper">
          @foreach($featured as $p)
            <div class="swiper-slide">
              @include('storefront.partials.product-card', ['p' => $p])
            </div>
          @endforeach
        </div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-button-next"></div>
      </div>
    </div>
  </section>
  @endif

</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  new Swiper('#hero-swiper', {
    loop: true,
    speed: 900,
    effect: 'fade',
    fadeEffect: { crossFade: true },
    autoplay: { delay: 5500, disableOnInteraction: false, pauseOnMouseEnter: true },
    pagination: { el: '#hero-swiper .swiper-pagination', clickable: true },
    navigation: {
      nextEl: '#hero-swiper .swiper-button-next',
      prevEl: '#hero-swiper .swiper-button-prev',
    },
  });

  new Swiper('#cat-swiper', {
    slidesPerView: 'auto',
    spaceBetween: 14,
    freeMode: true,
    grabCursor: true,
  });

  const promoEl = document.getElementById('promo-swiper');
  if (promoEl) {
    new Swiper('#promo-swiper', {
      loop: true,
      autoplay: { delay: 6000, disableOnInteraction: false },
      pagination: { el: '#promo-swiper .swiper-pagination', clickable: true },
    });
  }

  new Swiper('#feat-swiper', {
    slidesPerView: 'auto',
    spaceBetween: 18,
    grabCursor: true,
    navigation: {
      nextEl: '#feat-swiper .swiper-button-next',
      prevEl: '#feat-swiper .swiper-button-prev',
    },
    breakpoints: {
      640: { slidesPerView: 2 },
      900: { slidesPerView: 3 },
      1100: { slidesPerView: 4 },
    },
  });

  document.querySelectorAll('.offer-prod-swiper').forEach((el) => {
    el._swiper = new Swiper(el, {
      slidesPerView: 'auto',
      spaceBetween: 14,
      grabCursor: true,
      freeMode: true,
    });
  });
});

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
</script>
@endpush
