@extends('layouts.storefront')

@section('title', 'العروض — عبد القادر العطّار')

@push('head-styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
<style>
.page-hero{text-align:center;padding:36px 0 10px}
.page-hero h1{font-family:var(--font-thuluth);font-size:clamp(1.8rem,4vw,2.6rem);font-weight:400}
.page-hero p{color:var(--ink-soft);margin-top:6px}

.offers-toolbar{
  position:sticky;top:68px;z-index:40;
  background:linear-gradient(180deg,rgba(250,246,239,.97),rgba(250,246,239,.9));
  backdrop-filter:blur(8px);padding:14px 0 12px;border-bottom:1px solid var(--hair);margin-bottom:24px;
}
.offers-search{position:relative;max-width:560px;margin:0 auto 12px;display:flex;gap:10px;align-items:stretch}
.offers-search-field{position:relative;flex:1;min-width:0}
.offers-search input{
  width:100%;background:var(--card);border:1.5px solid var(--hair);border-radius:40px;
  padding:11px 44px 11px 16px;font-size:.95rem;color:var(--ink);outline:none;transition:.18s;
}
.offers-search input:focus{border-color:var(--gold)}
.offers-search-field svg{
  position:absolute;right:14px;top:50%;transform:translateY(-50%);
  width:18px;height:18px;color:var(--ink-soft);pointer-events:none;z-index:1;
}
.offers-search-btn{
  flex-shrink:0;background:linear-gradient(135deg,var(--gold),var(--saffron));color:#fff;border:none;
  padding:0 20px;border-radius:40px;font-family:var(--font-naskh);font-weight:700;font-size:.88rem;cursor:pointer;
  box-shadow:0 6px 18px -6px rgba(200,134,10,.4);white-space:nowrap;
}
.offers-filters{display:flex;gap:8px;flex-wrap:wrap;justify-content:center;align-items:center}
.offers-chip{
  background:var(--parchment-2);border:1px solid var(--hair);color:var(--ink-soft);
  padding:6px 14px;border-radius:30px;cursor:pointer;font-weight:600;font-size:.82rem;
  transition:.15s;white-space:nowrap;font-family:var(--font-ui);
}
.offers-chip:hover{border-color:var(--gold)}
.offers-chip.active{background:var(--ink);color:var(--parchment);border-color:var(--ink)}

.offer-block{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);
  padding:24px;margin-bottom:32px;box-shadow:0 12px 30px -20px rgba(36,26,17,.35)}
.offer-banner{height:180px;border-radius:14px;background-size:cover;background-position:center;margin-bottom:18px}
.offer-head{display:flex;justify-content:space-between;align-items:start;gap:16px;margin-bottom:20px;flex-wrap:wrap}
.offer-head h2{font-family:var(--font-thuluth);font-size:1.6rem;font-weight:400}
.offer-head p{color:var(--ink-soft);font-size:.92rem;margin-top:6px;max-width:600px;line-height:1.6}
.offer-meta{display:flex;flex-direction:column;gap:8px;align-items:flex-end}
.offer-discount{background:var(--clay);color:#fff;padding:7px 16px;border-radius:20px;font-weight:700}
.offer-countdown{background:#fef3c7;color:#92400e;padding:6px 12px;border-radius:20px;font-size:.82rem;font-weight:600}

.offer-prod-grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:20px}
.offer-prod-swiper-wrap{display:none;position:relative;padding:0 4px 8px}
.offer-prod-swiper .swiper-slide{width:min(78vw,260px);height:auto}
.offer-prod-swiper .card .thumb{height:140px}
.offer-prod-item[hidden]{display:none!important}
.offers-empty{text-align:center;padding:28px 16px;color:var(--ink-soft);font-size:.9rem}

.empty-offers{text-align:center;padding:60px 20px;color:var(--ink-soft)}

@media(max-width:768px){
  .offer-block{padding:16px;margin-bottom:20px}
  .offer-banner{height:140px}
  .offer-head{margin-bottom:14px}
  .offer-prod-grid{display:none}
  .offer-prod-swiper-wrap{display:block;padding:0 4px}
  .offers-toolbar{top:60px}
}
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

<div x-data="homeOffersFilter(@js($promoProductsMap))">
  <div class="wrap">
    <div class="page-hero">
      <h1>عروض العطّار</h1>
      <p>خصومات حصرية على أجود البهارات والمنتجات</p>
    </div>
  </div>

  @if(count($promotions))
  <div class="offers-toolbar">
    <div class="wrap">
      <div class="offers-search">
        <div class="offers-search-field">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input type="search" x-model="query" @input.debounce.200ms="applyFilter()" @keydown.enter.prevent="applyFilter()"
                 placeholder="ابحث في منتجات العروض…" autocomplete="off">
        </div>
        <button type="button" class="offers-search-btn" @click="applyFilter()">بحث</button>
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
  </div>
  @endif

  <div class="wrap">

  @forelse($promotions as $promo)
  <div class="offer-block" id="offer-{{ $promo['slug'] }}" x-show="promoHasVisible('{{ $promo['slug'] }}')" x-cloak>
    @if($promo['banner'])
      <div class="offer-banner" style="background-image:url('{{ $promo['banner'] }}')"></div>
    @endif

    <div class="offer-head">
      <div>
        <h2>{{ $promo['name'] }}</h2>
        @if($promo['description'])
          <p>{{ $promo['description'] }}</p>
        @endif
      </div>
      <div class="offer-meta">
        @if($promo['badge_text'])
          <span class="offer-discount">{{ $promo['badge_text'] }}</span>
        @else
          <span class="offer-discount">{{ $promo['discount_label'] }}</span>
        @endif
        @if($promo['show_countdown'] && $promo['days_remaining'] !== null)
          <span class="offer-countdown">⏳ ينتهي خلال {{ $promo['days_remaining'] }} أيام</span>
        @endif
      </div>
    </div>

    @if(count($promo['products']))
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
    @else
    <p class="empty-offers">لا توجد منتجات في هذا العرض حاليًا.</p>
    @endif
  </div>
  @empty
  <div class="empty-offers">
    <p>لا توجد عروض نشطة في الوقت الحالي.</p>
    <a href="{{ route('storefront.catalog') }}" class="btn-outline" style="margin-top:16px;display:inline-flex">تصفّح المنتجات</a>
  </div>
  @endforelse
  </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
  document.querySelectorAll('.offer-prod-swiper').forEach((el) => {
    el._swiper = new Swiper(el, {
      slidesPerView: 'auto',
      spaceBetween: 14,
      grabCursor: true,
      freeMode: true,
    });
  });
});
</script>
@endpush
