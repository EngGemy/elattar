@extends('layouts.storefront')

@section('title', $product->name . ' — عبد القادر العطّار')
@section('description', $product->short_description ?? $product->name)

@push('head-styles')
<style>
.breadcrumb{padding:20px 0 8px;font-size:.88rem;color:var(--ink-soft)}
.breadcrumb a{color:var(--ink-soft);text-decoration:none}
.breadcrumb a:hover{color:var(--gold-deep)}
.breadcrumb span{margin:0 6px}

.product-layout{display:grid;grid-template-columns:1fr 1fr;gap:48px;padding:20px 0 60px;align-items:start}
@media(max-width:780px){.product-layout{grid-template-columns:1fr;gap:28px}}

/* Gallery */
.gallery{position:sticky;top:100px}
.main-img{width:100%;aspect-ratio:1;border-radius:var(--radius);overflow:hidden;background:var(--parchment-2);
  border:1px solid var(--hair);margin-bottom:12px}
.main-img img{width:100%;height:100%;object-fit:cover}
.main-img .no-img{width:100%;height:100%;display:flex;align-items:center;justify-content:center;font-size:5rem}
.thumbs{display:flex;gap:10px;flex-wrap:wrap}
.thumb-item{width:72px;height:72px;border-radius:10px;overflow:hidden;border:2px solid transparent;
  cursor:pointer;transition:.18s;background:var(--parchment-2)}
.thumb-item img{width:100%;height:100%;object-fit:cover}
.thumb-item.active,.thumb-item:hover{border-color:var(--gold)}

/* Info */
.product-info{}
.product-info .cat-label{font-family:var(--font-ui);color:var(--clay);font-size:.82rem;letter-spacing:1.5px;font-weight:600}
.product-info h1{font-family:var(--font-thuluth);font-size:clamp(1.9rem,4.5vw,2.8rem);margin:6px 0 12px;line-height:1.45;font-weight:400}
.product-info .desc,.product-info p{font-family:var(--font-naskh);line-height:1.85}
.big-price{font-family:var(--font-naskh);color:var(--gold-deep);font-size:1.9rem;font-weight:700;margin-bottom:6px}
.big-price small{font-size:1rem;color:var(--ink-soft);font-weight:400}
.long-desc{color:var(--ink-soft);line-height:1.75;font-size:.96rem;margin:16px 0 24px}

/* Variant selector */
.variants-row{display:flex;gap:9px;flex-wrap:wrap;margin-bottom:20px}
.var-btn{background:var(--parchment-2);border:2px solid var(--hair);border-radius:10px;padding:8px 16px;
  cursor:pointer;font-weight:600;font-size:.9rem;color:var(--ink-soft);transition:.18s}
.var-btn:hover,.var-btn.active{border-color:var(--gold-deep);color:var(--gold-deep);background:var(--card)}

/* Qty controls */
.qty-section{background:var(--card);border:1.5px solid var(--hair);border-radius:16px;padding:18px 20px;margin-bottom:20px}
.qty-section h4{font-weight:700;margin-bottom:12px;font-size:.92rem;color:var(--ink-soft)}

.qty-strip{display:flex;align-items:center;gap:8px;background:var(--parchment-2);border:1px solid var(--hair);border-radius:14px;padding:8px 10px;margin-bottom:12px}
.qty-btn{width:42px;height:42px;border-radius:11px;border:none;background:#fff;
  font-size:1.35rem;cursor:pointer;transition:.15s;display:flex;align-items:center;justify-content:center;
  box-shadow:0 1px 4px rgba(42,24,16,.08);color:var(--ink);flex-shrink:0}
.qty-btn:hover{color:var(--gold-deep)}
.qty-input{flex:1;min-width:72px;text-align:center;border:none;border-radius:0;padding:6px 4px;
  font-size:1.15rem;font-weight:700;background:transparent;color:var(--ink);outline:none;-moz-appearance:textfield}
.qty-input::-webkit-outer-spin-button,.qty-input::-webkit-inner-spin-button{-webkit-appearance:none;margin:0}
.qty-unit{font-size:.88rem;color:var(--ink-soft);font-weight:600;flex-shrink:0}
.qty-strip-total{margin-inline-start:auto;padding-inline-start:12px;border-inline-start:1px solid var(--hair);
  font-size:.95rem;color:var(--gold-deep);font-weight:700;white-space:nowrap}

/* Quick weight chips */
.quick-weights{display:flex;gap:6px;flex-wrap:nowrap;overflow-x:auto;margin-bottom:10px;scrollbar-width:none;-webkit-overflow-scrolling:touch}
.quick-weights::-webkit-scrollbar{display:none}
.qw-btn{flex-shrink:0;background:var(--parchment);border:1px solid var(--hair);border-radius:20px;
  padding:7px 14px;font-size:.8rem;font-weight:600;cursor:pointer;transition:.15s;color:var(--ink-soft);font-family:var(--font-ui)}
.qw-btn:hover{border-color:rgba(200,134,10,.45);color:var(--ink)}
.qw-btn.active{background:var(--emerald);color:#fff;border-color:var(--emerald)}
.qw-btn--more{min-width:38px;text-align:center;background:transparent;font-weight:700}

.line-total{font-family:var(--font-naskh);font-size:.88rem;color:var(--ink-soft);font-weight:500;margin-top:4px}
.line-total strong{color:var(--gold-deep);font-size:1.05rem;font-weight:700}

.stock-badge{display:inline-flex;align-items:center;gap:6px;font-size:.85rem;font-weight:600;
  padding:5px 14px;border-radius:20px;margin-bottom:16px}
.in-stock{background:#d1fae5;color:#065f46}
.low-stock{background:#fef3c7;color:#92400e}
.out-of-stock{background:#fee2e2;color:#7f1d1d}

.add-to-cart-btn{width:100%;padding:15px;background:var(--ink);color:var(--parchment);border:none;
  border-radius:13px;font-size:1.05rem;font-weight:700;cursor:pointer;transition:.22s;
  display:flex;align-items:center;justify-content:center;gap:10px}
.add-to-cart-btn:hover{background:var(--gold-deep)}
.add-to-cart-btn:disabled{opacity:.4;cursor:not-allowed}
</style>
@endpush

@section('content')
<div class="wrap">
  {{-- Breadcrumb --}}
  <div class="breadcrumb">
    <a href="{{ route('storefront.home') }}">الرئيسية</a>
    <span>›</span>
    <a href="{{ route('storefront.catalog') }}">المنتجات</a>
    @if($product->category)
    <span>›</span>
    <a href="{{ route('storefront.catalog', ['category' => $product->category->slug]) }}">{{ $product->category->name }}</a>
    @endif
    <span>›</span>
    {{ $product->name }}
  </div>

  @php
    $defaultVariant = collect($variants)->firstWhere('is_default', true) ?? collect($variants)->first();
  @endphp

  <div class="product-layout"
       x-data="productPage(@js($variants->values()->all()), @js($defaultVariant))">

    {{-- Gallery --}}
    <div class="gallery">
      <div class="main-img">
        @if($mainImage)
          <img :src="activeImage" alt="{{ $product->name }}" id="main-product-img"
               x-init="activeImage = '{{ $mainImage }}'">
        @else
          <div class="no-img">🌿</div>
        @endif
      </div>

      @if($gallery->count() > 0)
      <div class="thumbs">
        @if($mainImage)
        <div class="thumb-item active" @click="activeImage = '{{ $mainImage }}'; $el.parentElement.querySelectorAll('.thumb-item').forEach(t=>t.classList.remove('active')); $el.classList.add('active')">
          <img src="{{ $mainImage }}" alt="">
        </div>
        @endif
        @foreach($gallery as $media)
        <div class="thumb-item" @click="activeImage = '{{ $media->getUrl('card') }}'; $el.parentElement.querySelectorAll('.thumb-item').forEach(t=>t.classList.remove('active')); $el.classList.add('active')">
          <img src="{{ $media->getUrl('thumb') }}" alt="">
        </div>
        @endforeach
      </div>
      @endif
    </div>

    {{-- Product Info --}}
    <div class="product-info">
      <p class="cat-label">{{ $product->category?->name }}</p>
      <h1>{{ $product->name }}</h1>

      {{-- Price --}}
      <div class="big-price">
        <span x-text="currentVariant ? fmt(currentVariant.price_minor) : '—'"></span>
        <small x-text="currentVariant?.is_weighted ? '/ كجم' : ('/ ' + (currentVariant?.unit_label ?? ''))"></small>
      </div>

      {{-- Stock badge --}}
      {{-- حالة التوفر فقط — بدون كشف الكمية --}}
      <div class="stock-badge"
           :class="stockStatus === 'out' ? 'out-of-stock' : 'in-stock'">
        <span x-show="stockStatus !== 'out'">● متاح</span>
        <span x-show="stockStatus === 'out'">✕ نفد المخزون</span>
      </div>

      {{-- Variants (if more than 1 active variant) --}}
      @if($variants->count() > 1)
      <div class="variants-row">
        <template x-for="v in variants" :key="v.id">
          <button @click="selectVariant(v)"
                  :class="currentVariant?.id === v.id ? 'active' : ''"
                  class="var-btn" x-text="v.unit_label || v.sku">
          </button>
        </template>
      </div>
      @endif

      {{-- Description --}}
      @if($product->long_description)
      <div class="long-desc">{{ $product->long_description }}</div>
      @elseif($product->short_description)
      <div class="long-desc">{{ $product->short_description }}</div>
      @endif

      {{-- Quantity selector --}}
      <div class="qty-section">
        <h4 x-text="currentVariant?.is_weighted ? 'اختر الكمية' : 'الكمية'"></h4>

        {{-- Quick weights (for weighted products) --}}
        <div x-show="currentVariant?.is_weighted">
          <div class="quick-weights">
            <template x-for="g in primaryQuickWeights" :key="g">
              <button type="button" @click="qty = g"
                      :class="qty === g ? 'active' : ''"
                      class="qw-btn"
                      x-text="weightLabel(g)"></button>
            </template>
            <button type="button" class="qw-btn qw-btn--more"
                    :class="showExtraQuickWeights ? 'active' : ''"
                    @click="showExtraQuickWeights = !showExtraQuickWeights"
                    x-text="showExtraQuickWeights ? 'أقل' : '···'"></button>
          </div>
          <div class="quick-weights" x-show="showExtraQuickWeights" x-transition.opacity.duration.200ms>
            <template x-for="g in extraQuickWeights" :key="g">
              <button type="button" @click="qty = g"
                      :class="qty === g ? 'active' : ''"
                      class="qw-btn"
                      x-text="weightLabel(g)"></button>
            </template>
          </div>
        </div>

        <div class="qty-strip">
          <button type="button" @click="decrement()" class="qty-btn">−</button>
          <input type="number" class="qty-input" x-model.number="qty"
                 :step="currentVariant?.step ?? 1"
                 :min="currentVariant?.step ?? 1"
                 @change="snapQty()">
          <span class="qty-unit" x-text="currentVariant?.is_weighted ? 'جم' : (currentVariant?.unit_label ?? '')"></span>
          <button type="button" @click="increment()" class="qty-btn">+</button>
          <span class="qty-strip-total" x-text="fmt(lineTotal)"></span>
        </div>

        <div class="line-total">
          الإجمالي: <strong x-text="fmt(lineTotal)"></strong>
        </div>
      </div>

      {{-- Add to cart --}}
      <form action="{{ route('storefront.cart.add') }}" method="POST" @submit.prevent="submitCart($el)">
        @csrf
        <input type="hidden" name="variant_id" :value="currentVariant?.id">
        <input type="hidden" name="qty" :value="qty">

        <button type="submit" class="add-to-cart-btn"
                :disabled="!currentVariant || !currentVariant.in_stock || qty <= 0">
          <svg width="20" height="20" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
          </svg>
          <span x-text="!currentVariant?.in_stock ? 'نفد المخزون' : ('أضف للسلة · ' + fmt(lineTotal))"></span>
        </button>
      </form>

    </div>
  </div>
</div>

@push('scripts')
<script>
function productPage(variants, defaultVariant) {
    return {
        variants,
        currentVariant: defaultVariant,
        qty: defaultVariant?.is_weighted ? 100 : (defaultVariant?.step ?? 1),
        activeImage: '{{ $mainImage }}',
        showExtraQuickWeights: false,
        primaryQuickWeights: [50, 100, 250, 500],
        extraQuickWeights: [1000, 25, 1],

        get stockStatus() {
            return this.currentVariant?.in_stock ? 'in' : 'out';
        },

        get lineTotal() {
            if (!this.currentVariant) return 0;
            return this.currentVariant.is_weighted
                ? Math.round(this.currentVariant.price_minor * this.qty / 1000)
                : Math.round(this.currentVariant.price_minor * this.qty);
        },

        selectVariant(v) {
            this.currentVariant = v;
            this.showExtraQuickWeights = false;
            this.qty = v.is_weighted ? 100 : (v.step ?? 1);
        },

        weightLabel(g) {
            if (g >= 1000) return (g / 1000) + ' كيلو';
            if (g === 500) return '½ كيلو';
            return g + ' جم';
        },

        increment() {
            const step = this.currentVariant?.step ?? 1;
            this.qty = +(this.qty + step).toFixed(3);
        },

        decrement() {
            const step = this.currentVariant?.step ?? 1;
            const next = +(this.qty - step).toFixed(3);
            if (next > 0) this.qty = next;
        },

        snapQty() {
            const v = this.currentVariant;
            if (!v) return;
            const step = v.step ?? 1;
            this.qty = Math.max(step, Math.round(this.qty / step) * step);
        },

        fmt(minor) {
            return ((minor || 0) / 100).toLocaleString('ar-EG', {
                minimumFractionDigits: 2, maximumFractionDigits: 2
            }) + ' ج.م';
        },

        submitCart(form) {
            form.querySelector('[name="variant_id"]').value = this.currentVariant?.id;
            form.querySelector('[name="qty"]').value = this.qty;
            form.submit();
        },
    };
}
</script>
@endpush
@endsection
