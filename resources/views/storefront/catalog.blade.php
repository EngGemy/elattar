@extends('layouts.storefront')

@section('title', 'المنتجات — ' . $shop['name'])

@push('head-styles')
<style>
/* ══ Catalog — mobile app ══ */
.cat-app{max-width:720px;margin:0 auto;padding-bottom:20px}

.cat-sticky{
  position:sticky;top:var(--chrome-h);z-index:45;
  background:rgba(238,241,238,.97);backdrop-filter:blur(14px);
  border-bottom:1px solid var(--hair);
  padding:8px 0 6px;
}
.cat-search{
  display:flex;gap:8px;align-items:stretch;padding:0 14px 8px;
}
.cat-search .box{position:relative;flex:1;min-width:0}
.cat-search input{
  width:100%;height:44px;border:1.5px solid var(--hair);border-radius:14px;
  padding:0 40px 0 12px;font-size:16px;background:var(--card);outline:none;
  font-family:var(--font-ui);
}
.cat-search input:focus{border-color:var(--gold)}
.cat-search svg{
  position:absolute;right:12px;top:50%;transform:translateY(-50%);
  width:17px;height:17px;color:var(--ink-soft);pointer-events:none;
}
.cat-search button{
  height:44px;padding:0 14px;border:none;border-radius:14px;
  background:var(--emerald);color:#fff;font-family:var(--font-ui);font-weight:700;font-size:.82rem;
}

.cat-scroll{
  display:flex;gap:7px;overflow-x:auto;padding:0 14px 6px;
  scroll-snap-type:x mandatory;-webkit-overflow-scrolling:touch;scrollbar-width:none;
}
.cat-scroll::-webkit-scrollbar{display:none}
.cat-chip{
  scroll-snap-align:start;flex:0 0 auto;
  padding:8px 12px;border-radius:999px;border:1px solid var(--hair);
  background:var(--card);color:var(--ink-soft);font-family:var(--font-ui);
  font-size:.76rem;font-weight:600;text-decoration:none;white-space:nowrap;
  transition:transform .2s,background .2s,color .2s;
}
.cat-chip:active{transform:scale(.96)}
.cat-chip.on{background:var(--night);color:var(--gold-light);border-color:transparent}

.cat-meta{
  display:flex;align-items:center;justify-content:space-between;gap:10px;
  padding:10px 14px 2px;
}
.cat-meta h1{font-family:var(--font-thuluth);font-size:clamp(1.15rem,4.5vw,1.35rem);font-weight:700;line-height:1.3}
.cat-meta .n{font-family:var(--font-ui);font-size:.72rem;color:var(--ink-soft)}
.cat-meta select{
  border:1px solid var(--hair);border-radius:12px;padding:7px 10px;
  background:var(--card);font-family:var(--font-ui);font-size:.76rem;font-weight:600;
}

.cat-grid{
  display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:10px;
  padding:10px 14px 32px;
}
@media(min-width:720px){
  .cat-grid{grid-template-columns:repeat(3,minmax(0,1fr));gap:16px;padding-inline:20px}
}
@media(min-width:1000px){
  .cat-grid{grid-template-columns:repeat(4,minmax(0,1fr))}
}

.cat-empty{
  text-align:center;padding:40px 18px;margin:10px 14px;
  background:var(--card);border:1px dashed var(--hair);border-radius:18px;
}
.cat-empty .ico{
  width:56px;height:56px;margin:0 auto 12px;border-radius:18px;
  background:linear-gradient(145deg,#1a3a2f,#0b1612);color:var(--gold);
  display:grid;place-items:center;font-size:1.25rem;
}
.cat-empty p{color:var(--ink-soft);font-size:.88rem;line-height:1.7;margin-bottom:16px}

.cat-pager{display:flex;gap:8px;justify-content:center;padding:4px 14px 32px;flex-wrap:wrap}
.cat-pager a,.cat-pager span{
  min-width:38px;height:38px;padding:0 10px;border-radius:12px;
  display:inline-flex;align-items:center;justify-content:center;
  border:1px solid var(--hair);background:var(--card);font-family:var(--font-ui);
  font-size:.82rem;font-weight:600;text-decoration:none;color:var(--ink-soft);
}
.cat-pager .active span{background:var(--emerald);color:#fff;border-color:var(--emerald)}

.cat-grid .card{animation:cardIn .45s cubic-bezier(.2,.8,.2,1) both}
.cat-grid .card:nth-child(1){animation-delay:.02s}
.cat-grid .card:nth-child(2){animation-delay:.06s}
.cat-grid .card:nth-child(3){animation-delay:.1s}
.cat-grid .card:nth-child(4){animation-delay:.14s}
.cat-grid .card:nth-child(5){animation-delay:.18s}
.cat-grid .card:nth-child(6){animation-delay:.22s}
@keyframes cardIn{from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:none}}
</style>
@endpush

@section('content')
@php
  $activeCat = request('category');
  $activeName = $activeCat
      ? ($categories->firstWhere('slug', $activeCat)?->name ?? 'نتائج البحث')
      : (request('q') ? 'نتائج البحث' : 'كل المنتجات');
@endphp

<div class="cat-app">
  <div class="cat-sticky">
    <form method="GET" action="{{ route('storefront.catalog') }}" id="filter-form">
      <div class="cat-search">
        <div class="box">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
          <input type="search" name="q" value="{{ request('q') }}" placeholder="ابحث عن منتج…" autocomplete="off">
        </div>
        <button type="submit">بحث</button>
      </div>

      <div class="cat-scroll" role="tablist" aria-label="التصنيفات">
        <a href="{{ route('storefront.catalog', request()->only('q', 'sort')) }}"
           class="cat-chip {{ ! $activeCat ? 'on' : '' }}">الكل</a>
        @foreach($categories as $cat)
          <a href="{{ route('storefront.catalog', array_merge(request()->only('q', 'sort'), ['category' => $cat->slug])) }}"
             class="cat-chip {{ $activeCat === $cat->slug ? 'on' : '' }}">{{ $cat->name }}</a>
        @endforeach
      </div>

      @if($activeCat)<input type="hidden" name="category" value="{{ $activeCat }}">@endif
      <input type="hidden" name="sort" id="sort-hidden" value="{{ $sort }}">
    </form>
  </div>

  <div class="cat-meta">
    <div>
      <h1>{{ $activeName }}</h1>
      <div class="n">{{ $products->total() }} منتج</div>
    </div>
    <select onchange="document.getElementById('sort-hidden').value=this.value;document.getElementById('filter-form').submit()" aria-label="ترتيب">
      <option value="newest" {{ $sort === 'newest' ? 'selected' : '' }}>الأحدث</option>
      <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>الأقل سعرًا</option>
      <option value="price_desc" {{ $sort === 'price_desc' ? 'selected' : '' }}>الأعلى سعرًا</option>
    </select>
  </div>

  @if($products->isEmpty())
    <div class="cat-empty">
      <div class="ico">◎</div>
      <p>لا توجد منتجات في هذا التصنيف.<br>جرّب تصنيفًا آخر أو امسح البحث.</p>
      <a href="{{ route('storefront.catalog') }}" class="btn-primary">عرض كل المنتجات</a>
    </div>
  @else
    <div class="cat-grid">
      @foreach($products as $p)
        @include('storefront.partials.product-card', ['p' => $p])
      @endforeach
    </div>
    @if($products->hasPages())
      <div class="cat-pager">
        {{ $products->onEachSide(1)->links('storefront.partials.pagination') }}
      </div>
    @endif
  @endif
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', () => {
  const on = document.querySelector('.cat-chip.on');
  if (on) on.scrollIntoView({ inline: 'center', block: 'nearest', behavior: 'smooth' });
});
</script>
@endpush
