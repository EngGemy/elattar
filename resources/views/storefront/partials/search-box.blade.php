{{-- صندوق بحث مع اقتراحات حية --}}
@php
  $initial = $initial ?? request('q', '');
  $formId = $formId ?? null;
  $placeholder = $placeholder ?? 'ابحث عن بهار أو منتج…';
  $compact = $compact ?? false;
@endphp
<div class="store-suggest {{ $compact ? 'is-compact' : '' }}"
     x-data="storeSuggest({
        initial: @js($initial),
        endpoint: @js(route('storefront.catalog.suggest')),
        catalogUrl: @js(route('storefront.catalog')),
     })"
     @keydown.escape.window="close()"
     @click.outside="close()">
  <div class="store-suggest-row">
    <div class="store-suggest-field">
      <svg class="store-suggest-ico" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
      </svg>
      <input type="search" name="q" x-model="q"
             @input.debounce.160ms="fetchSuggest()"
             @focus="onFocus()"
             @keydown.arrow-down.prevent="move(1)"
             @keydown.arrow-up.prevent="move(-1)"
             @keydown.enter.prevent="chooseOrSubmit()"
             placeholder="{{ $placeholder }}"
             autocomplete="off"
             enterkeyhint="search"
             role="combobox"
             :aria-expanded="open ? 'true' : 'false'"
             aria-autocomplete="list"
             aria-controls="store-suggest-list">
      <button type="button" class="store-suggest-clear" x-show="q.length" x-cloak @click="clear()" aria-label="مسح">×</button>
    </div>
    <button type="submit" @click.prevent="submit()">بحث</button>
  </div>

  <div class="store-suggest-panel" x-show="open" x-cloak x-transition.opacity.duration.150ms
       id="store-suggest-list" role="listbox">
    <template x-if="loading && !items.length">
      <div class="store-suggest-empty">جارٍ البحث…</div>
    </template>
    <template x-if="!loading && q.trim().length && !items.length">
      <div class="store-suggest-empty">
        لا توجد نتائج لـ «<span x-text="q.trim()"></span>»
        <button type="button" class="store-suggest-all" @click="submit()">عرض البحث الكامل</button>
      </div>
    </template>
    <template x-for="(item, idx) in items" :key="item.id">
      <button type="button" class="store-suggest-item" role="option"
              :class="idx === highlight && 'on'"
              @mousedown.prevent="go(item)"
              @mouseenter="highlight = idx">
        <span class="store-suggest-thumb">
          <img x-show="item.image" :src="item.image" :alt="item.name" loading="lazy">
          <span x-show="!item.image" class="store-suggest-fallback">ع</span>
        </span>
        <span class="store-suggest-meta">
          <strong x-text="item.name"></strong>
          <small>
            <span x-text="item.category || 'منتج'"></span>
            <span x-show="item.price_fmt"> · <span x-text="item.price_fmt"></span><span x-show="item.is_weighted"> / كجم</span></span>
          </small>
        </span>
        <span class="store-suggest-go" aria-hidden="true">←</span>
      </button>
    </template>
    <button type="button" class="store-suggest-footer" x-show="items.length" @mousedown.prevent="submit()">
      عرض كل النتائج لـ «<span x-text="q.trim()"></span>»
    </button>
  </div>
</div>
