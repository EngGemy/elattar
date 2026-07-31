@extends('layouts.storefront')

@section('title', $shop['name'] . ' — ' . $shop['tagline'])

@push('head-styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<style>
/* ══ Saffron Studio — cinematic mobile app home ══ */
.home{overflow-x:hidden}

/* Hero — full-bleed cinematic, mobile-safe */
.hx{
  position:relative;width:100%;
  height:clamp(360px, calc(100svh - var(--chrome-h) - 88px), 580px);
  max-height:68svh;overflow:hidden;isolation:isolate;
}
.hx-swiper,.hx-swiper .swiper-slide{height:100%;width:100%}
.hx-bg{position:absolute;inset:0}
.hx-bg img{
  width:100%;height:100%;object-fit:cover;object-position:center 40%;
  filter:saturate(1.04) contrast(1.04);
}
.hx-veil{
  position:absolute;inset:0;
  background:
    linear-gradient(180deg, rgba(11,22,18,.2) 0%, rgba(11,22,18,.5) 42%, rgba(11,22,18,.94) 100%),
    radial-gradient(ellipse 80% 55% at 85% 15%, rgba(224,162,26,.16), transparent 55%);
}
.hx-grain{
  position:absolute;inset:0;opacity:.18;pointer-events:none;mix-blend-mode:overlay;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='3'/%3E%3C/filter%3E%3Crect width='160' height='160' filter='url(%23n)' opacity='.55'/%3E%3C/svg%3E");
}
.hx-body{
  position:relative;z-index:2;height:100%;
  display:flex;flex-direction:column;justify-content:flex-end;
  padding:20px 16px 48px;color:#eef1ee;max-width:640px;box-sizing:border-box;
}
.hx-brand-logo{
  width:min(68vw,280px);height:auto;max-height:72px;object-fit:contain;object-position:right center;
  margin-bottom:10px;filter:drop-shadow(0 10px 24px rgba(0,0,0,.35));
  opacity:0;animation:rise .85s .08s cubic-bezier(.2,.85,.2,1) forwards;
}
.hx-brand-text{
  font-family:var(--font-thuluth);font-size:clamp(1.65rem,7vw,2.8rem);font-weight:700;
  color:var(--gold-light);line-height:1.2;margin-bottom:8px;
  opacity:0;animation:rise .85s .08s cubic-bezier(.2,.85,.2,1) forwards;
}
.hx-title{
  font-family:var(--font-thuluth);font-size:clamp(1.2rem,4.8vw,1.85rem);font-weight:600;
  line-height:1.35;margin:0 0 8px;max-width:18ch;
  opacity:0;animation:rise .8s .18s cubic-bezier(.2,.85,.2,1) forwards;
}
.hx-sub{
  font-size:clamp(.82rem,2.8vw,.92rem);line-height:1.65;color:rgba(238,241,238,.84);
  margin:0 0 16px;max-width:36ch;
  display:-webkit-box;-webkit-line-clamp:3;-webkit-box-orient:vertical;overflow:hidden;
  opacity:0;animation:rise .8s .28s cubic-bezier(.2,.85,.2,1) forwards;
}
.hx-cta{display:flex;flex-wrap:wrap;gap:8px;opacity:0;animation:rise .8s .38s cubic-bezier(.2,.85,.2,1) forwards}
.hx-cta .btn-primary,.hx-cta .btn-outline{padding:11px 18px;font-size:.84rem;border-radius:12px}
.hx-cta .btn-outline{color:#eef1ee;border-color:rgba(238,241,238,.45)}
.hx-cta .btn-outline:hover{border-color:var(--gold);color:var(--gold-light);background:rgba(224,162,26,.12)}
.hx .swiper-pagination{bottom:12px!important}
.hx .swiper-pagination-bullet{width:7px;height:7px;background:rgba(238,241,238,.35);opacity:1}
.hx .swiper-pagination-bullet-active{width:20px;border-radius:6px;background:var(--gold)}

@keyframes ken{from{transform:scale(1.04)}to{transform:scale(1.1) translate(-1%,.8%)}}
@keyframes rise{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:none}}
@keyframes floaty{0%,100%{transform:translateY(0)}50%{transform:translateY(-5px)}}

/* App quick actions */
.app-rail{
  display:grid;grid-template-columns:repeat(4,1fr);gap:8px;
  margin:-22px auto 0;padding:0 14px;max-width:560px;position:relative;z-index:5;
}
.app-rail a{
  display:flex;flex-direction:column;align-items:center;gap:6px;padding:12px 4px 10px;
  background:var(--card);border:1px solid var(--hair);border-radius:16px;
  box-shadow:0 12px 32px -16px rgba(11,22,18,.32);
  font-family:var(--font-ui);font-size:clamp(.62rem,2.6vw,.72rem);font-weight:600;color:var(--ink);
  transition:transform .3s cubic-bezier(.2,.8,.2,1);min-width:0;
}
.app-rail a:active{transform:scale(.96)}
.app-rail .ico{
  width:38px;height:38px;border-radius:12px;display:grid;place-items:center;
  background:linear-gradient(145deg,#1a3a2f,#0b1612);color:var(--gold);
  font-size:1rem;animation:floaty 4s ease-in-out infinite;
}
.app-rail a:nth-child(2) .ico{animation-delay:.3s}
.app-rail a:nth-child(3) .ico{animation-delay:.6s}
.app-rail a:nth-child(4) .ico{animation-delay:.9s}

.home-pad{padding:22px 0 28px}
.sec{margin-bottom:28px}
.sec.reveal{opacity:0;transform:translateY(22px);transition:opacity .65s cubic-bezier(.2,.8,.2,1),transform .65s cubic-bezier(.2,.8,.2,1)}
.sec.reveal.in{opacity:1;transform:none}
@media(prefers-reduced-motion:reduce){.sec.reveal{opacity:1;transform:none}}

.sec-bar{display:flex;align-items:flex-end;justify-content:space-between;gap:10px;margin-bottom:12px}
.sec-bar h2{font-family:var(--font-thuluth);font-size:clamp(1.25rem,5vw,1.55rem);font-weight:700;line-height:1.3;color:var(--ink)}
.sec-bar p{font-size:.76rem;color:var(--ink-soft);margin-top:2px}
.sec-bar a{font-family:var(--font-ui);font-size:.78rem;font-weight:700;color:var(--emerald);white-space:nowrap}

/* Categories snap */
.cat-snap{
  display:flex;gap:8px;overflow-x:auto;padding:2px 0 8px;
  scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;scrollbar-width:none;
}
.cat-snap::-webkit-scrollbar{display:none}
.cat-tile{
  scroll-snap-align:start;flex:0 0 auto;min-width:96px;
  padding:12px 12px;border-radius:16px;background:var(--card);border:1px solid var(--hair);
  text-align:center;font-family:var(--font-ui);font-weight:700;font-size:.78rem;
  box-shadow:0 8px 24px -16px rgba(11,22,18,.25);
  transition:transform .25s,border-color .25s;
}
.cat-tile:active{transform:scale(.96)}
.cat-tile.all{background:var(--night);color:var(--gold-light);border-color:transparent}
.cat-tile .dot{width:7px;height:7px;border-radius:50%;background:var(--gold);margin:0 auto 8px;box-shadow:0 0 0 3px rgba(224,162,26,.2)}

/* Featured grid */
.feat-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;padding-bottom:12px}
@media(min-width:760px){.feat-grid{grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}}

/* Promo cinema */
.promo-cine{
  display:block;position:relative;overflow:hidden;border-radius:20px;
  min-height:clamp(148px,42vw,200px);
  background:var(--night);color:#eef1ee;margin-bottom:10px;
  box-shadow:0 16px 40px -20px rgba(11,22,18,.4);
}
.promo-cine img{
  position:absolute;inset:0;width:100%;height:100%;
  object-fit:cover;object-position:center;opacity:.45;
}
.promo-cine .veil{position:absolute;inset:0;background:linear-gradient(115deg,rgba(11,22,18,.94),rgba(11,22,18,.4))}
.promo-cine .body{position:relative;z-index:1;padding:20px 16px;min-height:inherit;display:flex;flex-direction:column;justify-content:flex-end}
.promo-cine .tag{font-family:var(--font-ui);font-size:.7rem;font-weight:700;color:var(--gold);letter-spacing:.06em;margin-bottom:6px}
.promo-cine h3{font-family:var(--font-thuluth);font-size:clamp(1.2rem,4.5vw,1.45rem);font-weight:700;margin-bottom:6px;line-height:1.3}
.promo-cine p{
  font-size:.82rem;opacity:.82;line-height:1.55;margin-bottom:12px;max-width:34ch;
  display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;
}
.promo-cine .go{
  display:inline-flex;align-self:flex-start;padding:9px 14px;border-radius:11px;background:var(--gold);color:var(--night);
  font-family:var(--font-ui);font-weight:700;font-size:.8rem;
}

/* Story */
.story{
  border-radius:20px;padding:24px 16px;position:relative;overflow:hidden;
  background:
    radial-gradient(ellipse 70% 80% at 100% 0%, rgba(224,162,26,.2), transparent 50%),
    linear-gradient(145deg,#0b1612,#1a3a2f 60%,#142820);
  color:rgba(238,241,238,.88);
}
.story::before{
  content:'ع';position:absolute;font-family:var(--font-thuluth);font-size:7rem;font-weight:700;
  color:rgba(224,162,26,.08);inset-inline-end:2%;top:50%;transform:translateY(-50%);line-height:1;pointer-events:none;
}
.story h2{font-family:var(--font-thuluth);font-size:clamp(1.3rem,5vw,1.65rem);font-weight:700;color:var(--gold-light);margin-bottom:10px;position:relative}
.story p{font-size:.9rem;line-height:1.8;position:relative;max-width:42ch}
.story .meta{margin-top:14px;font-size:.76rem;opacity:.65;position:relative}

/* Marquee */
.mq{
  margin:10px 0 18px;overflow:hidden;border-block:1px solid rgba(26,58,47,.12);
  background:rgba(255,255,255,.55);padding:10px 0;
}
.mq-track{display:flex;width:max-content;gap:0;animation:mq 32s linear infinite}
.mq span{padding:0 22px;font-family:var(--font-ui);font-size:.74rem;font-weight:600;color:var(--emerald);white-space:nowrap}
.mq span::after{content:'✦';margin-inline-start:22px;color:var(--gold);opacity:.7}
@keyframes mq{to{transform:translateX(50%)}}

@media(min-width:760px){
  .hx{height:min(72vh,640px);max-height:none}
  .hx-bg img{animation:ken 16s ease-in-out infinite alternate}
  .hx-slide:nth-child(even) .hx-bg img{animation-direction:alternate-reverse}
  .hx-body{padding:40px 36px 56px}
  .hx-brand-logo{max-height:100px;width:min(50vw,320px);margin-bottom:14px}
  .hx-title{font-size:clamp(1.5rem,3vw,2rem)}
  .hx-sub{-webkit-line-clamp:4;font-size:.95rem;margin-bottom:22px}
  .hx-cta .btn-primary,.hx-cta .btn-outline{padding:13px 24px;font-size:.92rem}
  .app-rail{max-width:720px;gap:12px;margin-top:-32px;padding:0 20px}
  .app-rail a{padding:14px 8px 12px;font-size:.72rem;border-radius:18px}
  .app-rail .ico{width:42px;height:42px;border-radius:14px}
  .home-pad{padding:36px 0 64px}
  .sec{margin-bottom:42px}
  .sec-bar h2{font-size:1.85rem}
  .promo-cine{border-radius:24px;min-height:200px}
  .promo-cine .body{padding:28px 24px}
  .story{border-radius:24px;padding:36px 28px}
}
</style>
@endpush

@section('content')
<div class="home">

{{-- Cinematic hero --}}
<section class="hx" aria-label="واجهة المتجر">
  <div class="swiper hx-swiper" id="hx-swiper">
    <div class="swiper-wrapper">
      @foreach($heroSlides as $slide)
      <div class="swiper-slide hx-slide">
        <div class="hx-bg" aria-hidden="true">
          @if(!empty($slide['bg']))
            <img src="{{ $slide['bg'] }}" alt="" loading="{{ $loop->first ? 'eager' : 'lazy' }}">
          @endif
          <div class="hx-veil"></div>
          <div class="hx-grain"></div>
        </div>
        <div class="hx-body">
          @if(!empty($slide['is_brand']) && $shop['logo_url'])
            <img src="{{ $shop['logo_url'] }}" alt="{{ $shop['name'] }}" class="hx-brand-logo">
          @else
            <div class="hx-brand-text">{{ $shop['name'] }}</div>
          @endif
          <h1 class="hx-title">{{ $slide['title'] }}</h1>
          <p class="hx-sub">{{ $slide['subtitle'] }}</p>
          <div class="hx-cta">
            <a href="{{ $slide['cta'] }}" class="btn-primary">{{ $slide['cta_label'] }}</a>
            <a href="{{ route('storefront.offers') }}" class="btn-outline">العروض</a>
          </div>
        </div>
      </div>
      @endforeach
    </div>
    <div class="swiper-pagination"></div>
  </div>
</section>

{{-- App quick actions --}}
<nav class="app-rail" aria-label="اختصارات">
  <a href="{{ route('storefront.catalog') }}"><span class="ico">◈</span>المنتجات</a>
  <a href="{{ route('storefront.offers') }}"><span class="ico">٪</span>العروض</a>
  <a href="{{ route('storefront.track.lookup') }}"><span class="ico">◎</span>تتبّع</a>
  <a href="{{ \App\Support\ShopSettings::whatsappUrl('مرحبًا') }}" target="_blank" rel="noopener"><span class="ico">💬</span>واتساب</a>
</nav>

<div class="mq" aria-hidden="true">
  <div class="mq-track">
    @for($i=0;$i<2;$i++)
      <span>بهارات طازجة يوميًا</span>
      <span>بيع بالوزن من الجرام</span>
      <span>توصيل {{ implode(' و', array_slice($shop['delivery_cities'], 0, 2)) }}</span>
      <span>{{ $shop['name'] }}</span>
      <span>كاش · إنستاباي · فودافون كاش</span>
    @endfor
  </div>
</div>

<div class="wrap home-pad">

  @if($categories->count())
  <section class="sec reveal">
    <div class="sec-bar">
      <div>
        <h2>التصنيفات</h2>
        <p>اسحب واختر ما تحتاجه</p>
      </div>
      <a href="{{ route('storefront.catalog') }}">الكل ←</a>
    </div>
    <div class="cat-snap">
      <a href="{{ route('storefront.catalog') }}" class="cat-tile all"><div class="dot"></div>الكل</a>
      @foreach($categories as $cat)
        <a href="{{ route('storefront.catalog', ['category' => $cat->slug]) }}" class="cat-tile">
          <div class="dot"></div>{{ $cat->name }}
        </a>
      @endforeach
    </div>
  </section>
  @endif

  @if(count($featured))
  <section class="sec reveal">
    <div class="sec-bar">
      <div>
        <h2>الأكثر طلبًا</h2>
        <p>منتجات مختارة — أضف مباشرة</p>
      </div>
      <a href="{{ route('storefront.catalog') }}">المزيد ←</a>
    </div>
    <div class="feat-grid">
      @foreach($featured as $p)
        @include('storefront.partials.product-card', ['p' => $p])
      @endforeach
    </div>
  </section>
  @endif

  @if(count($homePromotions))
  <section class="sec reveal">
    <div class="sec-bar">
      <div>
        <h2>عروض الموسم</h2>
        <p>خصومات لفترة محدودة</p>
      </div>
      <a href="{{ route('storefront.offers') }}">كل العروض ←</a>
    </div>
    @foreach($homePromotions->take(2) as $promo)
      <a href="{{ route('storefront.offers') }}" class="promo-cine">
        @if($promo['banner'])
          <img src="{{ $promo['banner'] }}" alt="" loading="lazy">
        @endif
        <div class="veil"></div>
        <div class="body">
          <div class="tag">{{ $promo['discount_label'] }}</div>
          <h3>{{ $promo['name'] }}</h3>
          @if($promo['description'])
            <p>{{ \Illuminate\Support\Str::limit($promo['description'], 90) }}</p>
          @endif
          <span class="go">تسوّق العرض</span>
        </div>
      </a>
    @endforeach
  </section>
  @endif

  <section class="sec reveal">
    <div class="story">
      <h2>من قلب الدقهلية… لباب بيتك</h2>
      <p>{{ $shop['description'] }}</p>
      <div class="meta">
        {{ $shop['address'] }}
        @if($shop['phone']) · {{ $shop['phone'] }} @endif
      </div>
    </div>
  </section>

</div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  new Swiper('#hx-swiper', {
    loop: {{ count($heroSlides) > 1 ? 'true' : 'false' }},
    speed: 1100,
    effect: 'fade',
    fadeEffect: { crossFade: true },
    autoplay: {{ count($heroSlides) > 1 ? '{ delay: 5500, disableOnInteraction: false }' : 'false' }},
    pagination: { el: '#hx-swiper .swiper-pagination', clickable: true },
  });

  const io = new IntersectionObserver((entries) => {
    entries.forEach((e) => { if (e.isIntersecting) e.target.classList.add('in'); });
  }, { threshold: 0.08, rootMargin: '0px 0px -20px 0px' });
  document.querySelectorAll('.sec.reveal').forEach((el) => io.observe(el));
  // Fallback: never leave sections invisible
  setTimeout(() => {
    document.querySelectorAll('.sec.reveal:not(.in)').forEach((el) => el.classList.add('in'));
  }, 1200);
});
</script>
@endpush
