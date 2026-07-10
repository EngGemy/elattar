@extends('layouts.storefront')

@section('title', 'العروض — عبد القادر العطّار')

@push('head-styles')
<style>
.page-hero{text-align:center;padding:36px 0 10px}
.page-hero h1{font-family:'Amiri';font-size:clamp(2rem,4vw,2.8rem)}
.page-hero p{color:var(--ink-soft);margin-top:6px}

.offer-block{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);
  padding:24px;margin-bottom:32px;box-shadow:0 12px 30px -20px rgba(36,26,17,.35)}
.offer-banner{height:180px;border-radius:14px;background-size:cover;background-position:center;margin-bottom:18px}
.offer-head{display:flex;justify-content:space-between;align-items:start;gap:16px;margin-bottom:20px;flex-wrap:wrap}
.offer-head h2{font-family:'Amiri';font-size:1.7rem}
.offer-head p{color:var(--ink-soft);font-size:.92rem;margin-top:6px;max-width:600px;line-height:1.6}
.offer-meta{display:flex;flex-direction:column;gap:8px;align-items:flex-end}
.offer-discount{background:var(--clay);color:#fff;padding:7px 16px;border-radius:20px;font-weight:700}
.offer-countdown{background:#fef3c7;color:#92400e;padding:6px 12px;border-radius:20px;font-size:.82rem;font-weight:600}

.grid{display:grid;grid-template-columns:repeat(auto-fill,minmax(230px,1fr));gap:20px}
.card{background:var(--card);border:1px solid var(--hair);border-radius:var(--radius);overflow:hidden;
  box-shadow:0 12px 30px -20px rgba(36,26,17,.5);display:flex;flex-direction:column}
.card .thumb{height:180px;position:relative;overflow:hidden;background:var(--parchment-2)}
.badge-cat{position:absolute;top:11px;right:11px;background:rgba(36,26,17,.82);color:var(--parchment);
  font-family:'Reem Kufi';font-size:.7rem;padding:4px 11px;border-radius:20px}
.badge-sale{position:absolute;bottom:11px;right:11px;background:var(--clay);color:#fff;
  font-family:'Reem Kufi';font-size:.68rem;padding:4px 10px;border-radius:20px;font-weight:700}
.badge-stock{position:absolute;top:11px;left:11px;font-family:'Reem Kufi';font-size:.68rem;padding:4px 10px;border-radius:20px;font-weight:600}
.badge-stock.ok{background:#d1fae5;color:#065f46}
.badge-stock.low{background:#fef3c7;color:#92400e}
.badge-stock.no{background:var(--clay);color:#fff}
.card .body{padding:14px 16px 16px;display:flex;flex-direction:column;gap:9px;flex:1}
.card h3{font-family:'El Messiri';font-size:1.1rem;font-weight:700}
.card .desc{font-size:.84rem;color:var(--ink-soft);line-height:1.45;flex:1}
.card .price{font-family:'Reem Kufi';color:var(--gold-deep);font-weight:700;font-size:1.05rem;display:flex;align-items:baseline;gap:8px;flex-wrap:wrap}
.price-compare{color:var(--ink-soft);font-weight:500;font-size:.88rem;text-decoration:line-through;opacity:.75}
.unit-row{display:flex;gap:6px;flex-wrap:wrap}
.unit-opt{flex:1;min-width:58px;text-align:center;border:1px solid var(--hair);background:var(--parchment);
  border-radius:9px;padding:6px 3px;cursor:pointer;font-weight:600;font-size:.78rem;color:var(--ink-soft)}
.unit-opt.sel{background:var(--olive);color:#fff;border-color:var(--olive)}
.add-btn{background:var(--ink);color:var(--parchment);border:none;padding:10px;border-radius:11px;
  cursor:pointer;font-weight:600;font-size:.94rem;width:100%;margin-top:auto}

.empty-offers{text-align:center;padding:60px 20px;color:var(--ink-soft)}
</style>
@endpush

@section('content')
<div class="wrap">
  <div class="page-hero">
    <h1>عروض العطّار</h1>
    <p>خصومات حصرية على أجود البهارات والمنتجات</p>
  </div>

  @forelse($promotions as $promo)
  <div class="offer-block" id="offer-{{ $promo['slug'] }}">
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
    <div class="grid">
      @foreach($promo['products'] as $p)
        @include('storefront.partials.product-card', ['p' => $p])
      @endforeach
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
@endsection
