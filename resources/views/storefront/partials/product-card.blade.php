{{-- بطاقة منتج تفاعلية — كميات، مخزون، إضافة للسلة --}}
<div class="card" x-data="productCard(@js($p))">
    <div class="thumb">
        @if($p['image'])
            <img src="{{ $p['image'] }}" alt="{{ $p['name'] }}" loading="lazy">
        @else
            <div class="no-img">🫙</div>
        @endif
        <span class="badge-cat">{{ $p['category'] }}</span>
        @if(!empty($p['sale_badge']))
            <span class="badge-sale">{{ $p['sale_badge'] }}</span>
        @endif
        <span class="badge-stock" :class="stockClass()" x-text="stockLabel()"></span>
    </div>

    <div class="body">
        <h3>{{ $p['name'] }}</h3>
        @if(!empty($p['short_desc']))
            <p class="desc">{{ $p['short_desc'] }}</p>
        @endif

        <div class="price">
            <template x-if="variant?.compare_at_fmt">
                <del class="price-compare" x-text="variant.compare_at_fmt"></del>
            </template>
            <span x-text="priceLabel()"></span>
            <small x-text="unitSuffix()"></small>
        </div>

        {{-- اختيار المتغيّر --}}
        <template x-if="product.variants.length > 1">
            <div class="unit-row">
                <template x-for="v in product.variants" :key="v.id">
                    <button type="button" class="unit-opt"
                            :class="selectedVariantId === v.id ? 'sel' : ''"
                            :disabled="v.available <= 0"
                            @click="selectVariant(v.id)"
                            x-text="v.label"></button>
                </template>
            </div>
        </template>

        {{-- أوزان للبهارات --}}
        <template x-if="variant?.is_weighted && variant?.available > 0">
            <div class="unit-row">
                <template x-for="(w, i) in weights" :key="w.g">
                    <button type="button" class="unit-opt"
                            :class="weightIdx === i ? 'sel' : ''"
                            @click="weightIdx = i">
                        <span x-text="w.label"></span>
                        <small x-text="weightPrice(w.g)"></small>
                    </button>
                </template>
            </div>
        </template>

        {{-- كمية للقطع --}}
        <template x-if="variant && !variant.is_weighted && variant.available > 0">
            <div class="qty-row">
                <span class="unit-chip" x-text="variant.label"></span>
                <div class="stepper">
                    <button type="button" @click="pieceQty = Math.max(variant.step, pieceQty - variant.step)">−</button>
                    <span x-text="pieceQty"></span>
                    <button type="button" @click="pieceQty = Math.min(variant.available, pieceQty + variant.step)">+</button>
                </div>
            </div>
        </template>

        <button type="button" class="add-btn"
                :class="{ 'added': justAdded, 'disabled': !canAdd }"
                :disabled="!canAdd || loading"
                @click="addToCart()">
            <template x-if="!loading && !justAdded">
                <span class="add-btn-inner">
                    <svg width="16" height="16" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span x-text="canAdd ? 'أضف للسلة' : 'نفد المخزون'"></span>
                </span>
            </template>
            <template x-if="loading"><span>جارٍ الإضافة…</span></template>
            <template x-if="justAdded && !loading"><span>✓ تمت الإضافة</span></template>
        </button>
    </div>
</div>
