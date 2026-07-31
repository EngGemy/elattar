@extends('layouts.storefront')

@section('title', 'العروض — ' . $shop['name'])

@push('head-styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<style>
.off-app{max-width:720px;margin:0 auto;padding-bottom:28px}
.off-head{padding:18px 16px 8px}
.off-head h1{font-family:var(--font-thuluth);font-size:1.6rem;font-weight:700}
.off-head p{color:var(--ink-soft);font-size:.88rem;margin-top:4px}

.off-sticky{
  position:sticky;top:var(--chrome-h);z-index:45;
  background:rgba(238,241,238,.96);backdrop-filter:blur(14px);
  border-bottom:1px solid var(--hair);padding:10px 0;
}
.off-search{display:flex;gap:8px;padding:0 16px 8px}
.off-search .box{position:relative;flex:1}
.off-search input{
  width:100%;height:44px;border:1.5px solid var(--hair);border-radius:14px;
  padding:0 40px 0 12px;font-size:16px;background:var(--card);outline:none;font-family:var(--font-ui);
}
.off-search svg{position:absolute;right:12px;top:50%;transform:translateY(-50%);width:17px;height:17px;color:var(--ink-soft)}
.off-search button{height:44px;padding:0 14px;border:none;border-radius:14px;background:var(--emerald);color:#fff;font-weight:700;font-family:var(--font-ui);font-size:.82rem}
.off-scroll{display:flex;gap:8px;overflow-x:auto;padding:0 16px;scrollbar-width:none;scroll-snap-type:x mandatory}
.off-scroll::-webkit-scrollbar{display:none}
.off-chip{
  flex:0 0 auto;scroll-snap-align:start;padding:8px 14px;border-radius:999px;
  border:1px solid var(--hair);background:var(--card);font-family:var(--font-ui);font-size:.78rem;font-weight:600;color:var(--ink-soft);cursor:pointer;
}
.off-chip.on{background:var(--night);color:var(--gold-light);border-color:transparent}

.off-hero-wrap{margin:14px 16px 6px;border-radius:22px;overflow:hidden;box-shadow:0 16px 40px -20px rgba(11,22,18,.4)}
.off-hero{
  position:relative;min-height:168px;background:var(--night);color:#eef1ee;
}
.off-hero img{position:absolute;inset:0;width:100%;height:100%;object-fit:cover;opacity:.4}
.off-hero .veil{position:absolute;inset:0;background:linear-gradient(120deg,rgba(11,22,18,.92),rgba(11,22,18,.35))}
.off-hero .body{position:relative;z-index:1;padding:22px;min-height:168px;display:flex;flex-direction:column;justify-content:flex-end}
.off-hero .tag{font-family:var(--font-ui);font-size:.72rem;color:var(--gold);font-weight:700;margin-bottom:6px}
.off-hero h2{font-family:var(--font-thuluth);font-size:1.4rem;font-weight:700;margin-bottom:6px}
.off-hero p{font-size:.84rem;opacity:.8;line-height:1.6}
.off-hero-wrap .swiper-pagination{bottom:10px!important}
.off-hero-wrap .swiper-pagination-bullet{background:rgba(238,241,238,.4);opacity:1}
.off-hero-wrap .swiper-pagination-bullet-active{background:var(--gold);width:18px;border-radius:6px}

.off-block{padding:8px 16px 20px}
.off-block-title{display:flex;justify-content:space-between;align-items:baseline;gap:8px;margin-bottom:12px}
.off-block-title h3{font-family:var(--font-thuluth);font-size:1.25rem;font-weight:700}
.off-block-title span{font-family:var(--font-ui);font-size:.75rem;color:var(--emerald);font-weight:700}

.off-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:12px}
@media(min-width:720px){.off-grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:14px}}
.offer-prod-item[hidden]{display:none!important}

.off-empty{text-align:center;padding:40px 20px;color:var(--ink-soft);font-size:.9rem}
.off-none{text-align:center;padding:48px 20px;margin:16px;background:var(--card);border-radius:20px;border:1px dashed var(--hair);color:var(--ink-soft)}
</style>
@endpush

@section('content')
@php
  $offerProductCategories = collect($promotions)
      ->flatMap(fn ($promo) => $promo['products'])
      ->filter(fn ($p) => ! empty($p['category_slug']))
      ->unique('category_slug')
      ->sortBy('category')
      ->values();
  $promoProductsMap = collect($promotions)->mapWithKeys(function ($promo) {
      return [$promo['slug'] => collect($promo['products'])->map(fn ($p) => [
          'name' => $p['name'],
          'category_slug' => $p['category_slug'] ?? '',
      ])->values()->all()];
  })->all();
@endphp

<div class="off-app" x-data="homeOffersFilter(@js($promoProductsMap))">
  <div class="off-head">
    <h1>العروض</h1>
    <p>خصومات لفترة محدودة على مختارات العطّار</p>
  </div>

  @if(count($promotions))
  <div class="off-sticky">
    <div class="off-search">
      <div class="box">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
        <input type="search" x-model="query" @input.debounce.200ms="applyFilter()" placeholder="ابحث في العروض…">
      </div>
      <button type="button" @click="applyFilter()">بحث</button>
    </div>
    <div class="off-scroll">
      <button type="button" class="off-chip" :class="category === '' && 'on'" @click="setCategory('')">الكل</button>
      @foreach($offerProductCategories as $oc)
        <button type="button" class="off-chip" :class="category === '{{ $oc['category_slug'] }}' && 'on'" @click="setCategory('{{ $oc['category_slug'] }}')">{{ $oc['category'] }}</button>
      @endforeach
    </div>
  </div>

  <div class="off-hero-wrap">
    <div class="swiper" id="off-hero-swiper">
      <div class="swiper-wrapper">
        @foreach($promotions as $promo)
        <div class="swiper-slide">
          <div class="off-hero">
            @if($promo['banner'])<img src="{{ $promo['banner'] }}" alt="" loading="lazy">@endif
            <div class="veil"></div>
            <div class="body">
              <div class="tag">{{ $promo['discount_label'] }}</div>
              <h2>{{ $promo['name'] }}</h2>
              @if($promo['description'])<p>{{ \Illuminate\Support\Str::limit($promo['description'], 100) }}</p>@endif
            </div>
          </div>
        </div>
        @endforeach
      </div>
      @if(count($promotions) > 1)
      <div class="swiper-pagination"></div>
      @endif
    </div>
  </div>

  @foreach($promotions as $promo)
    @if(count($promo['products']))
    <div class="off-block" x-show="promoHasVisible('{{ $promo['slug'] }}')" x-cloak>
      <div class="off-block-title">
        <h3>{{ $promo['name'] }}</h3>
        <span>{{ $promo['discount_label'] }}</span>
      </div>
      <div class="off-grid">
        @foreach($promo['products'] as $p)
          <div class="offer-prod-item"
               data-promo="{{ $promo['slug'] }}"
               :hidden="!matches(@js(['name' => $p['name'], 'category_slug' => $p['category_slug'] ?? '']))">
            @include('storefront.partials.product-card', ['p' => $p])
          </div>
        @endforeach
      </div>
      <div class="off-empty" x-show="!promoHasVisible('{{ $promo['slug'] }}')">لا توجد منتجات مطابقة في هذا العرض.</div>
    </div>
    @endif
  @endforeach
  @else
    <div class="off-none">
      <p>لا توجد عروض حالياً — تصفّح المنتجات كاملة.</p>
      <a href="{{ route('storefront.catalog') }}" class="btn-primary" style="margin-top:14px;display:inline-flex">المنتجات</a>
    </div>
  @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', () => {
  const el = document.getElementById('off-hero-swiper');
  if (!el || typeof Swiper === 'undefined') return;
  new Swiper(el, {
    loop: el.querySelectorAll('.swiper-slide').length > 1,
    autoplay: { delay: 4200, disableOnInteraction: false },
    pagination: { el: el.querySelector('.swiper-pagination'), clickable: true },
  });
});
</script>
@endpush
