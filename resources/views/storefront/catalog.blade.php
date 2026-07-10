@extends('layouts.storefront')

@section('title', 'المنتجات — عبد القادر العطّار')

@push('head-styles')
<style>
/* Toolbar */
.toolbar{position:sticky;top:68px;z-index:40;background:linear-gradient(180deg,rgba(241,233,216,.97),rgba(241,233,216,.9));
  backdrop-filter:blur(8px);padding:16px 0 12px;border-bottom:1px solid var(--hair);margin-bottom:28px}
.search-row{display:flex;gap:10px;align-items:stretch;max-width:560px;margin:0 auto 14px}
.search-box{position:relative;flex:1;min-width:0}
.search-box input{width:100%;background:var(--card);border:1.5px solid var(--hair);border-radius:40px;
  padding:12px 46px 12px 18px;font-size:1rem;color:var(--ink);outline:none;transition:.18s;font-family:var(--font-naskh)}
.search-box input:focus{border-color:var(--gold)}
.search-box svg{position:absolute;right:16px;top:50%;transform:translateY(-50%);width:20px;height:20px;color:var(--ink-soft);pointer-events:none;z-index:1}
.search-btn{
  flex-shrink:0;background:linear-gradient(135deg,var(--gold),var(--saffron));color:#fff;border:none;
  padding:0 22px;border-radius:40px;font-family:var(--font-naskh);font-weight:700;font-size:.92rem;cursor:pointer;
  box-shadow:0 6px 20px -6px rgba(200,134,10,.4);transition:.2s;white-space:nowrap;
}
.search-btn:hover{transform:translateY(-1px);box-shadow:0 10px 24px -6px rgba(200,134,10,.5)}
.filters{display:flex;gap:9px;flex-wrap:wrap;justify-content:center;align-items:center}
.chip{background:var(--parchment-2);border:1px solid var(--hair);color:var(--ink-soft);padding:7px 18px;
  border-radius:30px;cursor:pointer;font-weight:600;font-size:.9rem;transition:.15s;white-space:nowrap;text-decoration:none;
  font-family:var(--font-naskh)}
.chip:hover{border-color:var(--gold)}
.chip.active{background:var(--ink);color:var(--parchment);border-color:var(--ink)}
.sort-sel{background:var(--card);border:1.5px solid var(--hair);border-radius:30px;padding:7px 14px;
  font-size:.88rem;font-weight:600;color:var(--ink);outline:none;cursor:pointer}

/* Grid */
.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(220px,1fr));gap:20px;padding-bottom:16px}
.card{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);overflow:hidden;
  box-shadow:0 12px 30px -20px rgba(36,26,17,.4);display:flex;flex-direction:column;
  transition:transform .22s,box-shadow .22s}
.card:hover{transform:translateY(-4px);box-shadow:0 20px 40px -20px rgba(36,26,17,.6)}
.card .thumb{height:170px;overflow:hidden;background:var(--parchment-2);position:relative}
.card .thumb img{width:100%;height:100%;object-fit:cover;transition:transform .5s}
.card:hover .thumb img{transform:scale(1.07)}
.card .thumb .no-img{width:100%;height:100%;display:flex;align-items:center;justify-content:center;color:var(--hair);font-size:2.5rem}
.badge-cat{position:absolute;top:10px;right:10px;background:rgba(36,26,17,.82);color:var(--parchment);
  font-family:var(--font-ui);font-size:.7rem;padding:3px 10px;border-radius:20px}
.badge-oos{position:absolute;top:10px;left:10px;background:var(--clay);color:#fff;
  font-family:var(--font-ui);font-size:.7rem;padding:3px 10px;border-radius:20px}
.card .body{padding:13px 15px 15px;display:flex;flex-direction:column;gap:8px;flex:1}
.card h3{font-family:var(--font-naskh);font-size:1.08rem;font-weight:600;line-height:1.55}
.card .desc{font-family:var(--font-naskh)}
.card .price{font-family:var(--font-naskh);color:var(--gold-deep);font-weight:700;font-size:1rem}
.card .price small{color:var(--ink-soft);font-weight:400;font-size:.76rem}
.badge-stock{position:absolute;top:10px;left:10px;font-family:'Reem Kufi';font-size:.68rem;padding:3px 9px;border-radius:20px;font-weight:600}
.badge-stock.ok{background:#d1fae5;color:#065f46}
.badge-stock.low{background:#fef3c7;color:#92400e}
.badge-stock.no{background:var(--clay);color:#fff}
.badge-sale{position:absolute;bottom:10px;right:10px;background:var(--clay);color:#fff;
  font-family:var(--font-ui);font-size:.68rem;padding:4px 10px;border-radius:20px;font-weight:700}
.card .price{display:flex;align-items:baseline;gap:8px;flex-wrap:wrap}
.price-compare{color:var(--ink-soft);font-weight:500;font-size:.88rem;text-decoration:line-through;opacity:.75}
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
.add-btn{background:var(--ink);color:var(--parchment);border:none;padding:9px;border-radius:11px;
  cursor:pointer;font-weight:600;font-size:.9rem;width:100%;transition:.2s;margin-top:auto}
.add-btn .add-btn-inner{display:flex;align-items:center;justify-content:center;gap:6px}
.add-btn:hover:not(:disabled){background:var(--gold-deep)}
.add-btn.added{background:var(--olive)}
.add-btn.disabled{opacity:.45;cursor:not-allowed}

/* Pagination */
.pagination{display:flex;gap:8px;justify-content:center;padding:24px 0 48px;flex-wrap:wrap}
.pagination a,.pagination span{padding:8px 16px;border-radius:30px;font-size:.9rem;font-weight:600;
  border:1.5px solid var(--hair);background:var(--card);color:var(--ink-soft);transition:.15s;text-decoration:none}
.pagination a:hover{border-color:var(--gold);color:var(--gold-deep)}
.pagination .active span{background:var(--ink);color:var(--parchment);border-color:var(--ink)}
.pagination span.disabled{opacity:.4;cursor:default}

.empty-state{text-align:center;padding:60px 20px;color:var(--ink-soft)}
.empty-state p{font-size:1.1rem;margin-top:12px}
</style>
@endpush

@section('content')
{{-- Sticky toolbar --}}
<div class="toolbar">
  <div class="wrap">
    <form method="GET" action="{{ route('storefront.catalog') }}" id="filter-form">
      <div class="search-row">
        <div class="search-box">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
          </svg>
          <input type="search" name="q" value="{{ request('q') }}" placeholder="ابحث عن منتج…" autocomplete="off">
        </div>
        <button type="submit" class="search-btn">بحث</button>
      </div>

      <div class="filters">
        <a href="{{ route('storefront.catalog', array_merge(request()->except('category'), ['sort' => $sort])) }}"
           class="chip {{ ! request('category') ? 'active' : '' }}">الكل</a>

        @foreach($categories as $cat)
        <a href="{{ route('storefront.catalog', array_merge(request()->except('category'), ['category' => $cat->slug, 'sort' => $sort])) }}"
           class="chip {{ request('category') === $cat->slug ? 'active' : '' }}">
          @if($cat->icon && !str_starts_with($cat->icon, 'heroicon'))<span>{{ $cat->icon }}</span>@endif
          {{ $cat->name }}
        </a>
        @endforeach

        <select name="sort" class="sort-sel" onchange="this.form.submit()">
          <option value="newest"    {{ $sort === 'newest'    ? 'selected' : '' }}>الأحدث</option>
          <option value="price_asc" {{ $sort === 'price_asc' ? 'selected' : '' }}>السعر: الأقل</option>
          <option value="price_desc"{{ $sort === 'price_desc'? 'selected' : '' }}>السعر: الأعلى</option>
        </select>

        @if(request('category'))<input type="hidden" name="category" value="{{ request('category') }}">@endif
      </div>
    </form>
  </div>
</div>

<div class="wrap">
  @if($products->isEmpty())
    <div class="empty-state">
      <span style="font-size:3rem">🌿</span>
      <p>لا توجد منتجات مطابقة. جرّب تغيير الفلتر أو البحث بكلمة أخرى.</p>
      <a href="{{ route('storefront.catalog') }}" class="btn-primary" style="margin-top:20px;display:inline-flex">عرض الكل</a>
    </div>
  @else
    <div class="grid">
      @foreach($products as $p)
        @include('storefront.partials.product-card', ['p' => $p])
      @endforeach
    </div>

    {{-- Pagination --}}
    @if($products->hasPages())
    <div class="pagination">
      {{ $products->onEachSide(1)->links('storefront.partials.pagination') }}
    </div>
    @endif
  @endif
</div>
@endsection
