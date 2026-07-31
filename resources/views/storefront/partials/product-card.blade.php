{{-- بطاقة منتج — عرض مدمج واحترافي --}}
<div class="card card-product" x-data="productCard(@js($p))">
    <a href="{{ route('storefront.product', $p['slug']) }}" class="thumb thumb-link" aria-label="{{ $p['name'] }}">
        @if($p['image'])
            <img src="{{ $p['image'] }}" alt="{{ $p['name'] }}" loading="lazy">
        @else
            <div class="no-img">ع</div>
        @endif
        <span class="badge-cat">{{ $p['category'] }}</span>
        @if(!empty($p['sale_badge']))
            <span class="badge-sale">{{ $p['sale_badge'] }}</span>
        @endif
        <span class="badge-stock" x-show="!canAdd" x-cloak :class="stockClass()" x-text="stockLabel()"></span>
    </a>

    <div class="body">
        <div class="card-head">
            <h3>
                <a href="{{ route('storefront.product', $p['slug']) }}">{{ $p['name'] }}</a>
            </h3>
            @if(!empty($p['short_desc']))
                <p class="desc">{{ $p['short_desc'] }}</p>
            @endif
        </div>

        <div class="price">
            <template x-if="variant?.compare_at_fmt">
                <del class="price-compare" x-text="variant.compare_at_fmt"></del>
            </template>
            <span x-text="priceLabel()"></span>
            <small x-text="unitSuffix()"></small>
        </div>

        <template x-if="product.variants.length > 1">
            <div class="variant-chips">
                <template x-for="v in product.variants" :key="v.id">
                    <button type="button" class="weight-chip"
                            :class="selectedVariantId === v.id ? 'sel' : ''"
                            :disabled="!v.in_stock"
                            @click="selectVariant(v.id)"
                            x-text="v.label"></button>
                </template>
            </div>
        </template>

        <div class="card-purchase">
            <template x-if="variant?.is_weighted && variant?.in_stock">
                <div class="weight-panel">
                    <div class="weight-strip" dir="ltr">
                        <button type="button" class="weight-step-btn" @click="adjustWeight(-1)" aria-label="تقليل">−</button>
                        <div class="weight-mid">
                            <input type="number" class="weight-input" x-model.number="weightGrams"
                                   :min="variant.step || 1" :step="variant.step || 1"
                                   @change="snapWeight()" aria-label="الكمية بالجرام">
                            <span class="weight-unit">جم</span>
                        </div>
                        <button type="button" class="weight-step-btn" @click="adjustWeight(1)" aria-label="زيادة">+</button>
                    </div>

                    <div class="weight-chips" role="group" aria-label="كميات سريعة">
                        <template x-for="w in primaryWeights" :key="w.g">
                            <button type="button" class="weight-chip"
                                    :class="weightGrams === w.g ? 'sel' : ''"
                                    @click="weightGrams = w.g; snapWeight()"
                                    x-text="w.label"></button>
                        </template>
                        <template x-if="extraWeights.length">
                            <button type="button" class="weight-chip weight-chip--more"
                                    :class="showExtraWeights ? 'sel' : ''"
                                    @click="showExtraWeights = !showExtraWeights"
                                    x-text="showExtraWeights ? 'أقل' : '···'"></button>
                        </template>
                    </div>

                    <div class="weight-chips weight-chips--extra" x-show="showExtraWeights" x-transition.opacity.duration.200ms>
                        <template x-for="w in extraWeights" :key="w.g">
                            <button type="button" class="weight-chip"
                                    :class="weightGrams === w.g ? 'sel' : ''"
                                    @click="weightGrams = w.g; snapWeight()"
                                    x-text="w.label"></button>
                        </template>
                    </div>
                </div>
            </template>

            <template x-if="variant && !variant.is_weighted && variant.in_stock">
                <div class="piece-panel">
                    <span class="unit-chip" x-text="variant.label"></span>
                    <div class="weight-strip" dir="ltr">
                        <button type="button" class="weight-step-btn"
                                @click="pieceQty = Math.max(variant.step, pieceQty - variant.step)" aria-label="تقليل">−</button>
                        <div class="weight-mid">
                            <span class="piece-qty" x-text="pieceQty"></span>
                        </div>
                        <button type="button" class="weight-step-btn"
                                @click="pieceQty = pieceQty + variant.step" aria-label="زيادة">+</button>
                    </div>
                </div>
            </template>

            <button type="button" class="add-btn"
                    :class="{ 'added': justAdded, 'disabled': !canAdd }"
                    :disabled="!canAdd || loading"
                    @click="addToCart()">
                <template x-if="!loading && !justAdded">
                    <span class="add-btn-inner">
                        <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                        </svg>
                        <span class="add-btn-txt" x-text="canAdd ? 'أضف للسلة' : 'نفد المخزون'"></span>
                        <span class="add-btn-price" x-show="canAdd" x-text="fmt(lineTotalMinor()) + ' ج.م'"></span>
                    </span>
                </template>
                <template x-if="loading"><span>جارٍ الإضافة…</span></template>
                <template x-if="justAdded && !loading"><span>✓ تمت الإضافة</span></template>
            </button>
        </div>
    </div>
</div>
