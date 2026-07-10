<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use App\Domain\Shared\Enums\ProductStatus;
use App\Domain\Shared\Enums\ProductType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

/**
 * المنتج.
 * قاعدة معمارية: المنتج البسيط له متغيّر افتراضي واحد مخفي.
 * النتيجة: المخزون والطلبات و POS تتعامل مع variant_id فقط — صفر شروط.
 */
class Product extends Model implements HasMedia, AuditableContract
{
    use HasFactory, SoftDeletes, HasSlug, InteractsWithMedia, Auditable;

    protected $fillable = [
        'category_id', 'tax_class_id', 'sku_root', 'name', 'slug', 'type',
        'short_description', 'long_description', 'status',
        'has_variants', 'is_featured', 'sort_order',
    ];

    protected $casts = [
        'type'         => ProductType::class,
        'status'       => ProductStatus::class,
        'has_variants' => 'boolean',
        'is_featured'  => 'boolean',
    ];

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()->generateSlugsFrom('name')->saveSlugsTo('slug');
    }

    // ── الوسائط: صورة رئيسية + معرض
    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('main')->singleFile();
        $this->addMediaCollection('gallery');
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(200)->height(200)->nonQueued();
        $this->addMediaConversion('card')->width(400)->height(400);
    }

    // ── العلاقات
    public function category(): BelongsTo  { return $this->belongsTo(Category::class); }
    public function taxClass(): BelongsTo  { return $this->belongsTo(TaxClass::class); }
    public function variants(): HasMany    { return $this->hasMany(ProductVariant::class); }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(ProductVariant::class)->where('is_default', true);
    }

    public function relatedProducts(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_relations', 'product_id', 'related_product_id')
            ->withPivot('relation_type', 'sort_order')
            ->wherePivot('relation_type', 'related');
    }

    public function crossSells(): BelongsToMany
    {
        return $this->belongsToMany(Product::class, 'product_relations', 'product_id', 'related_product_id')
            ->withPivot('relation_type')
            ->wherePivot('relation_type', 'cross_sell');
    }

    // ── Scopes
    public function scopeActive($q)   { return $q->where('status', ProductStatus::Active); }
    public function scopeFeatured($q) { return $q->where('is_featured', true); }

    /** إجمالي المتاح عبر كل المخازن */
    public function totalAvailable(): float
    {
        return (float) $this->variants()
            ->join('stock_levels', 'stock_levels.variant_id', '=', 'product_variants.id')
            ->sum(\DB::raw('stock_levels.on_hand - stock_levels.reserved'));
    }

    public function isOutOfStock(): bool
    {
        return $this->totalAvailable() <= 0;
    }

    public function taxRate(): float
    {
        return $this->taxClass?->ratePercent() ?? 0.0;
    }
}
