@extends('layouts.storefront')

@section('title', $shop['name'] . ' — ' . $shop['tagline'])

@push('head-styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<style>
/* ══ Saffron Studio — cinematic mobile app home ══ */
.home{overflow-x:hidden}

/* Hero */
.hx{
  position:relative;width:100%;height:min(72svh,620px);min-height:420px;
  overflow:hidden;isolation:isolate;
}
.hx-swiper,.hx-swiper .swiper-slide{height:100%;width:100%}
.hx-bg{position:absolute;inset:0}
.hx-bg img{
  width:100%;height:100%;object-fit:cover;
  transform:scale(1.12);animation:ken 14s ease-in-out infinite alternate;
  filter:saturate(1.05) contrast(1.05);
}
.hx-slide:nth-child(even) .hx-bg img{animation-direction:alternate-reverse}
.hx-veil{
  position:absolute;inset:0;
  background:
    linear-gradient(180deg, rgba(11,22,18,.25) 0%, rgba(11,22,18,.55) 45%, rgba(11,22,18,.92) 100%),
    radial-gradient(ellipse 80% 60% at 80% 20%, rgba(224,162,26,.18), transparent 55%);
}
.hx-grain{
  position:absolute;inset:0;opacity:.22;pointer-events:none;mix-blend-mode:overlay;
  background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='160' height='160'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.85' numOctaves='3'/%3E%3C/filter%3E%3Crect width='160' height='160' filter='url(%23n)' opacity='.55'/%3E%3C/svg%3E");
}
.hx-body{
  position:relative;z-index:2;height:100%;
  display:flex;flex-direction:column;justify-content:flex-end;
  padding:28px 20px 52px;color:#eef1ee;max-width:640px;
}
.hx-brand-logo{
  width:min(78vw,340px);height:auto;max-height:110px;object-fit:contain;object-position:right center;
  margin-bottom:14px;filter:drop-shadow(0 12px 28px rgba(0,0,0,.4));
  opacity:0;animation:rise .9s .1s cubic-bezier(.2,.85,.2,1) forwards;
}
.hx-brand-text{
  font-family:var(--font-thuluth);font-size:clamp(2rem,8vw,3.2rem);font-weight:700;
  color:var(--gold-light);line-height:1.2;margin-bottom:10px;
  opacity:0;animation:rise .9s .1s cubic-bezier(.2,.85,.2,1) forwards;
}
.hx-title{
  font-family:var(--font-thuluth);font-size:clamp(1.35rem,4.5vw,2rem);font-weight:600;
  line-height:1.35;margin:0 0 10px;max-width:16ch;
  opacity:0;animation:rise .85s .22s cubic-bezier(.2,.85,.2,1) forwards;
}
.hx-sub{
  font-size:.92rem;line-height:1.75;color:rgba(238,241,238,.82);margin:0 0 22px;max-width:34ch;
  opacity:0;animation:rise .85s .34s cubic-bezier(.2,.85,.2,1) forwards;
}
.hx-cta{display:flex;flex-wrap:wrap;gap:10px;opacity:0;animation:rise .85s .46s cubic-bezier(.2,.85,.2,1) forwards}
.hx-cta .btn-outline{color:#eef1ee;border-color:rgba(238,241,238,.45)}
.hx-cta .btn-outline:hover{border-color:var(--gold);color:var(--gold-light);background:rgba(224,162,26,.12)}
.hx .swiper-pagination{bottom:16px!important}
.hx .swiper-pagination-bullet{width:7px;height:7px;background:rgba(238,241,238,.35);opacity:1}
.hx .swiper-pagination-bullet-active{width:22px;border-radius:6px;background:var(--gold)}

@keyframes ken{from{transform:scale(1.12) translate(0,0)}to{transform:scale(1.22) translate(-2%,1.5%)}}
@keyframes rise{from{opacity:0;transform:translateY(28px) scale(.98)}to{opacity:1;transform:none}}
@keyframes floaty{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}

/* App quick actions */
.app-rail{
  display:grid;grid-template-columns:repeat(4,1fr);gap:8px;
  margin:-28px auto 0;padding:0 16px;max-width:560px;position:relative;z-index:5;
}
.app-rail a{
  display:flex;flex-direction:column;align-items:center;gap:8px;padding:14px 6px 12px;
  background:var(--card);border:1px solid var(--hair);border-radius:18px;
  box-shadow:0 14px 36px -18px rgba(11,22,18,.35);
  font-family:var(--font-ui);font-size:.72rem;font-weight:600;color:var(--ink);
  transition:transform .3s cubic-bezier(.2,.8,.2,1);
}
.app-rail a:active{transform:scale(.96)}
.app-rail .ico{
  width:42px;height:42px;border-radius:14px;display:grid;place-items:center;
  background:linear-gradient(145deg,#1a3a2f,#0b1612);color:var(--gold);
  font-size:1.1rem;animation:floaty 4s ease-in-out infinite;
}
.app-rail a:nth-child(2) .ico{animation-delay:.3s}
.app-rail a:nth-child(3) .ico{animation-delay:.6s}
.app-rail a:nth-child(4) .ico{animation-delay:.9s}

.home-pad{padding:28px 0 48px}
.sec{margin-bottom:36px}
.sec.reveal{opacity:0;transform:translateY(28px);transition:opacity .7s cubic-bezier(.2,.8,.2,1),transform .7s cubic-bezier(.2,.8,.2,1)}
.sec.reveal.in{opacity:1;transform:none}

.sec-bar{display:flex;align-items:flex-end;justify-content:space-between;gap:12px;margin-bottom:16px;padding-inline:2px}
.sec-bar h2{font-family:var(--font-thuluth);font-size:1.55rem;font-weight:700;line-height:1.3;color:var(--ink)}
.sec-bar p{font-size:.8rem;color:var(--ink-soft);margin-top:2px}
.sec-bar a{font-family:var(--font-ui);font-size:.8rem;font-weight:700;color:var(--emerald);white-space:nowrap}

/* Categories snap */
.cat-snap{
  display:flex;gap:10px;overflow-x:auto;padding:4px 2px 12px;
  scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;scrollbar-width:none;
}
.cat-snap::-webkit-scrollbar{display:none}
.cat-tile{
  scroll-snap-align:start;flex:0 0 auto;min-width:108px;
  padding:16px 14px;border-radius:18px;background:var(--card);border:1px solid var(--hair);
  text-align:center;font-family:var(--font-ui);font-weight:700;font-size:.82rem;
  box-shadow:0 8px 24px -16px rgba(11,22,18,.25);
  transition:transform .25s,border-color .25s;
}
.cat-tile:active{transform:scale(.96)}
.cat-tile.all{background:var(--night);color:var(--gold-light);border-color:transparent}
.cat-tile .dot{width:8px;height:8px;border-radius:50%;background:var(--gold);margin:0 auto 10px;box-shadow:0 0 0 4px rgba(224,162,26,.2)}

/* Featured grid */
.feat-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
@media(min-width:760px){.feat-grid{grid-template-columns:repeat(4,minmax(0,1fr));gap:16px}}
.feat-grid .card .thumb{height:140px}
@media(max-width:400px){.feat-grid .card .thumb{height:120px}}

/* Promo cinema */
.promo-cine{
  display:block;position:relative;overflow:hidden;border-radius:24px;min-height:180px;
  background:var(--night);color:#eef1ee;margin-bottom:12px;
  box-shadow:0 20px 50px -24px rgba(11,22,18,.45);
}
.promo-cine img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.4;transform:scale(1.08);transition:transform 1.2s}
.promo-cine:hover img{transform:scale(1.14)}
.promo-cine .veil{position:absolute;inset:0;background:linear-gradient(115deg,rgba(11,22,18,.92),rgba(11,22,18,.35))}
.promo-cine .body{position:relative;z-index:1;padding:28px 22px}
.promo-cine .tag{font-family:var(--font-ui);font-size:.72rem;font-weight:700;color:var(--gold);letter-spacing:.08em;margin-bottom:8px}
.promo-cine h3{font-family:var(--font-thuluth);font-size:1.55rem;font-weight:700;margin-bottom:8px;line-height:1.3}
.promo-cine p{font-size:.88rem;opacity:.8;line-height:1.65;margin-bottom:16px;max-width:36ch}
.promo-cine .go{
  display:inline-flex;padding:10px 18px;border-radius:12px;background:var(--gold);color:var(--night);
  font-family:var(--font-ui);font-weight:700;font-size:.85rem;
}

/* Story */
.story{
  border-radius:24px;padding:32px 22px;position:relative;overflow:hidden;
  background:
    radial-gradient(ellipse 70% 80% at 100% 0%, rgba(224,162,26,.2), transparent 50%),
    linear-gradient(145deg,#0b1612,#1a3a2f 60%,#142820);
  color:rgba(238,241,238,.88);
}
.story::before{
  content:'ع';position:absolute;font-family:var(--font-thuluth);font-size:9rem;font-weight:700;
  color:rgba(224,162,26,.08);inset-inline-end:4%;top:50%;transform:translateY(-50%);line-height:1;pointer-events:none;
}
.story h2{font-family:var(--font-thuluth);font-size:1.7rem;font-weight:700;color:var(--gold-light);margin-bottom:12px;position:relative}
.story p{font-size:.95rem;line-height:1.85;position:relative;max-width:42ch}
.story .meta{margin-top:18px;font-size:.8rem;opacity:.65;position:relative}

/* Marquee */
.mq{
  margin:8px 0 28px;overflow:hidden;border-block:1px solid rgba(26,58,47,.12);
  background:rgba(255,255,255,.55);padding:12px 0;
}
.mq-track{display:flex;width:max-content;gap:0;animation:mq 32s linear infinite}
.mq span{padding:0 28px;font-family:var(--font-ui);font-size:.78rem;font-weight:600;color:var(--emerald);white-space:nowrap}
.mq span::after{content:'✦';margin-inline-start:28px;color:var(--gold);opacity:.7}
@keyframes mq{to{transform:translateX(50%)}}

@media(min-width:900px){
  .hx{height:min(78vh,680px)}
  .hx-body{padding:48px 40px 64px}
  .app-rail{max-width:720px;gap:12px;margin-top:-36px}
  .home-pad{padding:40px 0 72px}
  .sec{margin-bottom:48px}
  .sec-bar h2{font-size:2rem}
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
  }, { threshold: 0.12, rootMargin: '0px 0px -40px 0px' });
  document.querySelectorAll('.sec.reveal').forEach((el) => io.observe(el));
});
</script>
@endpush
