@extends('layouts.storefront')

@section('title', $shop['name'] . ' — ' . $shop['tagline'])

@push('head-styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<style>
/* ══ Copper Vault Home — brand-first, full-bleed hero ══ */
.home-hero{
  position:relative;width:100%;max-width:100vw;overflow:hidden;
  margin:0;padding:0;
}
.hero-swiper{height:calc(100svh - var(--chrome-h));min-height:520px;max-height:820px;width:100%}
.hero-swiper .swiper-slide{position:relative;overflow:hidden}

/* Full-bleed plane — no inset cards */
.hero-bleed{
  position:absolute;inset:0;
  background:
    radial-gradient(ellipse 70% 55% at 70% 40%, rgba(201,146,46,.22), transparent 55%),
    radial-gradient(ellipse 50% 60% at 15% 80%, rgba(47,74,56,.35), transparent 50%),
    linear-gradient(165deg, #0c0a08 0%, #1a1510 45%, #241c14 100%);
}
.hero-bleed::before{
  content:'';position:absolute;inset:0;opacity:.35;pointer-events:none;mix-blend-mode:overlay;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.8' numOctaves='3' stitchTiles='stitch'/%3E%3C/filter%3E%3Crect width='180' height='180' filter='url(%23n)' opacity='.55'/%3E%3C/svg%3E");
}
.hero-bleed-img{
  position:absolute;inset:0;width:100%;height:100%;object-fit:cover;
  opacity:.42;filter:saturate(.85) contrast(1.05);
  transform:scale(1.04);animation:heroDrift 18s ease-in-out infinite alternate;
}
.hero-bleed-veil{
  position:absolute;inset:0;
  background:linear-gradient(105deg, rgba(12,10,8,.88) 0%, rgba(12,10,8,.55) 48%, rgba(12,10,8,.35) 100%);
}
.hero-content{
  position:relative;z-index:2;height:100%;
  display:flex;flex-direction:column;justify-content:flex-end;
  padding:clamp(28px,6vw,64px) clamp(20px,5vw,56px) clamp(56px,8vw,80px);
  max-width:720px;color:#f6f3ee;
  animation:heroRise .9s cubic-bezier(.2,.8,.2,1) both;
}
.hero-brand{
  font-family:var(--font-thuluth);font-size:clamp(2.6rem,8vw,4.8rem);font-weight:400;
  line-height:1.25;color:var(--gold-light);margin:0 0 10px;
  text-shadow:0 8px 40px rgba(0,0,0,.45);
  letter-spacing:.01em;
}
.hero-brand-logo{
  width:min(88vw,520px);height:auto;max-height:min(28vh,180px);
  object-fit:contain;object-position:right center;margin-bottom:18px;
  filter:drop-shadow(0 16px 40px rgba(201,146,46,.35));
  animation:heroRise 1s .05s cubic-bezier(.2,.8,.2,1) both;
}
.hero-line{
  font-family:var(--font-thuluth);font-size:clamp(1.35rem,3.2vw,2rem);
  color:#fff;line-height:1.45;margin:0 0 12px;font-weight:400;max-width:28ch;
}
.hero-sub{
  font-family:var(--font-naskh);font-size:clamp(.95rem,2vw,1.08rem);font-weight:500;
  color:rgba(246,243,238,.78);line-height:1.85;margin:0 0 28px;max-width:38ch;
}
.hero-cta{display:flex;flex-wrap:wrap;gap:12px}
.hero-cta .btn-primary{
  background:linear-gradient(135deg,var(--gold),var(--saffron));
  color:var(--night);border:none;box-shadow:0 12px 32px -8px rgba(201,146,46,.55);
}
.hero-cta .btn-outline{color:#f6f3ee;border-color:rgba(246,243,238,.4)}
.hero-cta .btn-outline:hover{border-color:var(--gold-light);color:var(--gold-light);background:rgba(201,146,46,.12)}

.hero-swiper .swiper-pagination{bottom:22px!important}
.hero-swiper .swiper-pagination-bullet{
  width:8px;height:8px;background:rgba(246,243,238,.35);opacity:1;transition:.25s;
}
.hero-swiper .swiper-pagination-bullet-active{
  width:26px;border-radius:6px;background:var(--gold);
}
.hero-swiper .swiper-button-prev,.hero-swiper .swiper-button-next{
  color:var(--gold-light);background:rgba(12,10,8,.45);width:44px;height:44px;border-radius:50%;
  border:1px solid rgba(201,146,46,.3);backdrop-filter:blur(8px);top:auto;bottom:28px;
}
.hero-swiper .swiper-button-prev{right:auto;left:20px}
.hero-swiper .swiper-button-next{left:auto;right:20px}
.hero-swiper .swiper-button-prev:after,.hero-swiper .swiper-button-next:after{font-size:1rem;font-weight:900}

@keyframes heroRise{from{opacity:0;transform:translateY(24px)}to{opacity:1;transform:none}}
@keyframes heroDrift{from{transform:scale(1.04) translate(0,0)}to{transform:scale(1.1) translate(-1.5%,1%)}}

/* Trust strip — AFTER first viewport */
.home-trust{
  display:grid;grid-template-columns:repeat(3,1fr);gap:0;
  background:var(--ink);color:rgba(246,243,238,.7);
  border-bottom:1px solid rgba(201,146,46,.25);
}
.home-trust > div{
  padding:18px 16px;text-align:center;font-family:var(--font-ui);font-size:.8rem;
  border-inline-start:1px solid rgba(201,146,46,.15);
}
.home-trust > div:first-child{border-inline-start:none}
.home-trust strong{display:block;color:var(--gold-light);font-size:.95rem;margin-bottom:2px;font-weight:600}

/* Sections */
.home-main{padding:clamp(40px,6vw,72px) 0 24px}
.sec{margin-bottom:clamp(48px,7vw,80px)}
.sec-head{margin-bottom:28px;max-width:560px}
.sec-over{font-family:var(--font-ui);font-size:.72rem;letter-spacing:.18em;color:var(--copper);margin-bottom:8px}
.sec-title{font-family:var(--font-thuluth);font-size:clamp(1.85rem,3.8vw,2.7rem);color:var(--ink);line-height:1.4;font-weight:400}
.sec-sub{color:var(--ink-soft);font-size:.95rem;margin-top:8px;line-height:1.75}
.sec-link{font-family:var(--font-ui);font-size:.85rem;font-weight:600;color:var(--gold-deep);display:inline-block;margin-top:10px}

/* Categories — one purpose */
.cat-rail{display:flex;gap:12px;overflow-x:auto;padding-bottom:8px;scrollbar-width:none;-webkit-overflow-scrolling:touch}
.cat-rail::-webkit-scrollbar{display:none}
.cat-pill{
  flex-shrink:0;display:flex;align-items:center;gap:10px;
  padding:14px 20px;border-radius:14px;min-width:max-content;
  background:var(--card);border:1px solid var(--hair);
  font-family:var(--font-ui);font-weight:600;font-size:.88rem;color:var(--ink);
  transition:border-color .2s,transform .2s,box-shadow .2s;
  animation:heroRise .7s both;
}
.cat-pill:hover{border-color:rgba(201,146,46,.45);transform:translateY(-3px);box-shadow:var(--shadow)}
.cat-pill.star{background:var(--ink);color:var(--gold-light);border-color:rgba(201,146,46,.35)}
.cat-pill .mark{
  width:8px;height:8px;border-radius:50%;background:var(--gold);flex-shrink:0;
  box-shadow:0 0 0 3px rgba(201,146,46,.2);
}

/* Story band — atmosphere, not cards */
.home-story{
  margin:0 calc(-1 * clamp(12px,4vw,24px));
  padding:clamp(48px,7vw,88px) clamp(20px,5vw,56px);
  position:relative;overflow:hidden;
  background:
    radial-gradient(ellipse 60% 80% at 90% 50%, rgba(201,146,46,.12), transparent 55%),
    linear-gradient(120deg, #16120e 0%, #1f1914 55%, #2a2218 100%);
  color:rgba(246,243,238,.82);
}
.home-story::after{
  content:'ع';position:absolute;font-family:var(--font-thuluth);font-size:clamp(8rem,22vw,18rem);
  color:rgba(201,146,46,.06);inset-inline-end:4%;top:50%;transform:translateY(-50%);pointer-events:none;line-height:1;
}
.home-story-inner{position:relative;z-index:1;max-width:640px}
.home-story h2{
  font-family:var(--font-thuluth);font-size:clamp(1.9rem,4vw,2.8rem);
  color:var(--gold-light);line-height:1.4;margin-bottom:16px;font-weight:400;
}
.home-story p{font-size:1.05rem;line-height:1.9;font-weight:500}
.home-story .meta{margin-top:28px;font-family:var(--font-ui);font-size:.85rem;color:rgba(246,243,238,.55)}
.home-story .meta a{color:var(--gold-light);border-bottom:1px solid rgba(201,146,46,.35)}

/* Promo ribbon */
.promo-ribbon{
  display:block;position:relative;overflow:hidden;min-height:200px;
  border-radius:0;margin:0 calc(-1 * clamp(12px,4vw,24px)) 36px;
  background:var(--ink);
}
.promo-ribbon img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.35}
.promo-ribbon-veil{position:absolute;inset:0;background:linear-gradient(100deg,rgba(12,10,8,.92),rgba(12,10,8,.45))}
.promo-ribbon-body{
  position:relative;z-index:1;padding:clamp(32px,5vw,48px) clamp(20px,5vw,56px);
  color:#f6f3ee;max-width:560px;
}
.promo-ribbon-body .disc{
  font-family:var(--font-ui);font-size:.78rem;font-weight:700;color:var(--gold);
  letter-spacing:.12em;margin-bottom:10px;
}
.promo-ribbon-body h3{font-family:var(--font-thuluth);font-size:clamp(1.5rem,3vw,2.1rem);margin-bottom:10px;font-weight:400}
.promo-ribbon-body p{opacity:.8;margin-bottom:20px;line-height:1.7}

/* Featured */
.prod-swiper-wrap{position:relative;padding:0 0 48px}
.prod-swiper .swiper-slide{height:auto;width:252px;display:flex}
.prod-swiper .swiper-slide .card{width:100%}
.prod-swiper .card .thumb{height:156px}
.prod-swiper .swiper-button-prev,.prod-swiper .swiper-button-next{
  top:auto;bottom:0;color:var(--ink);background:var(--card);width:40px;height:40px;border-radius:50%;
  border:1px solid var(--hair);box-shadow:var(--shadow);
}
.prod-swiper .swiper-button-prev{right:48px;left:auto}
.prod-swiper .swiper-button-next{right:0;left:auto}
.prod-swiper .swiper-button-prev:after,.prod-swiper .swiper-button-next:after{font-size:.85rem;font-weight:900}

@media(max-width:768px){
  .hero-swiper{min-height:460px;max-height:none;height:calc(100svh - var(--chrome-h))}
  .hero-swiper .swiper-button-prev,.hero-swiper .swiper-button-next{display:none}
  .home-trust{grid-template-columns:1fr; }
  .home-trust > div{border-inline-start:none;border-top:1px solid rgba(201,146,46,.12);padding:14px}
  .home-trust > div:first-child{border-top:none}
  .hero-content{justify-content:center;padding-bottom:64px}
}
</style>
@endpush

@section('content')

{{-- ══ HERO: brand + one line + CTA + full-bleed atmosphere ══ --}}
<section class="home-hero" aria-label="واجهة المتجر">
  <div class="swiper hero-swiper" id="hero-swiper">
    <div class="swiper-wrapper">
      @foreach($heroSlides as $slide)
        <div class="swiper-slide">
          <div class="hero-bleed" aria-hidden="true">
            @if(!empty($slide['bg']))
              <img src="{{ $slide['bg'] }}" alt="" class="hero-bleed-img" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
            @endif
            <div class="hero-bleed-veil"></div>
          </div>
          <div class="hero-content">
            @if(!empty($slide['is_brand']) && $shop['logo_url'])
              <img src="{{ $shop['logo_url'] }}" alt="{{ $shop['name'] }}" class="hero-brand-logo">
            @else
              <p class="hero-brand">{{ $shop['name'] }}</p>
            @endif
            <h1 class="hero-line">{{ $slide['title'] }}</h1>
            <p class="hero-sub">{{ $slide['subtitle'] }}</p>
            <div class="hero-cta">
              <a href="{{ $slide['cta'] }}" class="btn-primary">{{ $slide['cta_label'] }}</a>
              <a href="{{ route('storefront.offers') }}" class="btn-outline">العروض</a>
            </div>
          </div>
        </div>
      @endforeach
    </div>
    <div class="swiper-pagination"></div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
  </div>
</section>

<div class="home-trust" aria-label="معلومات سريعة">
  <div><strong>طازج يوميًا</strong>بهارات مختارة بعناية</div>
  <div><strong>توصيل {{ $shop['governorate'] }}</strong>{{ implode(' · ', array_slice($shop['delivery_cities'], 0, 3)) }}</div>
  <div><strong>دفع مرن</strong>كاش · إنستاباي · فودافون كاش</div>
</div>

<div class="wrap home-main">

  {{-- تصنيفات — مهمة واحدة --}}
  @if($categories->count())
  <section class="sec">
    <div class="sec-head">
      <div class="sec-over">التصنيفات</div>
      <h2 class="sec-title">تسوق من الرف</h2>
      <p class="sec-sub">اختر التصنيف وابدأ الطلب بالوزن أو بالقطعة.</p>
      <a href="{{ route('storefront.catalog') }}" class="sec-link">كل المنتجات ←</a>
    </div>
    <div class="cat-rail">
      <a href="{{ route('storefront.catalog') }}" class="cat-pill star"><span class="mark"></span>الكل</a>
      @foreach($categories as $i => $cat)
        <a href="{{ route('storefront.catalog', ['category' => $cat->slug]) }}"
           class="cat-pill" style="animation-delay:{{ min($i * 0.05, 0.4) }}s">
          <span class="mark"></span>{{ $cat->name }}
        </a>
      @endforeach
    </div>
  </section>
  @endif

  {{-- قصة العلامة — بدون كروت --}}
  <section class="sec home-story">
    <div class="home-story-inner">
      <h2>تراث العطارة… طعم البيت</h2>
      <p>{{ $shop['description'] }}</p>
      <div class="meta">
        {{ $shop['address'] }}
        @if($shop['phone'])
          · <a href="tel:{{ $shop['phone'] }}">{{ $shop['phone'] }}</a>
        @endif
      </div>
    </div>
  </section>

  {{-- عروض --}}
  @if(count($homePromotions))
  <section class="sec">
    <div class="sec-head">
      <div class="sec-over">الآن</div>
      <h2 class="sec-title">عروض الموسم</h2>
      <p class="sec-sub">خصومات محدودة على مختارات {{ $shop['name'] }}.</p>
      <a href="{{ route('storefront.offers') }}" class="sec-link">كل العروض ←</a>
    </div>

    @foreach($homePromotions->take(2) as $promo)
      <a href="{{ route('storefront.offers') }}" class="promo-ribbon">
        @if($promo['banner'])
          <img src="{{ $promo['banner'] }}" alt="" loading="lazy">
        @endif
        <div class="promo-ribbon-veil"></div>
        <div class="promo-ribbon-body">
          <div class="disc">{{ $promo['discount_label'] }}</div>
          <h3>{{ $promo['name'] }}</h3>
          @if($promo['description'])
            <p>{{ \Illuminate\Support\Str::limit($promo['description'], 110) }}</p>
          @endif
          <span class="btn-primary" style="pointer-events:none">تسوّق العرض</span>
        </div>
      </a>
    @endforeach
  </section>
  @endif

  {{-- مميّز --}}
  @if(count($featured))
  <section class="sec">
    <div class="sec-head">
      <div class="sec-over">اختياراتنا</div>
      <h2 class="sec-title">منتجات مميّزة</h2>
      <p class="sec-sub">الأكثر طلبًا — بالوزن أو بالعبوة.</p>
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
    loop: {{ count($heroSlides) > 1 ? 'true' : 'false' }},
    speed: 900,
    effect: 'fade',
    fadeEffect: { crossFade: true },
    autoplay: {{ count($heroSlides) > 1 ? '{ delay: 6000, disableOnInteraction: false, pauseOnMouseEnter: true }' : 'false' }},
    pagination: { el: '#hero-swiper .swiper-pagination', clickable: true },
    navigation: {
      nextEl: '#hero-swiper .swiper-button-next',
      prevEl: '#hero-swiper .swiper-button-prev',
    },
  });

  const feat = document.getElementById('feat-swiper');
  if (feat) {
    new Swiper('#feat-swiper', {
      slidesPerView: 'auto',
      spaceBetween: 16,
      grabCursor: true,
      navigation: {
        nextEl: '#feat-swiper .swiper-button-next',
        prevEl: '#feat-swiper .swiper-button-prev',
      },
    });
  }
});
</script>
@endpush
