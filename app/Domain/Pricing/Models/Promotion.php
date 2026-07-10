<?php

declare(strict_types=1);

namespace App\Domain\Pricing\Models;

use App\Casts\MoneyCast;
use App\Domain\Catalog\Models\Category;
use App\Domain\Catalog\Models\Product;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Pricing\Enums\PromotionDiscountType;
use App\Domain\Pricing\Enums\PromotionScope;
use App\Domain\Shared\ValueObjects\Money;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Cache;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

final class Promotion extends Model implements HasMedia, AuditableContract
{
    use Auditable, HasSlug, InteractsWithMedia, SoftDeletes;

    protected $fillable = [
        'name', 'slug', 'description',
        'discount_type', 'discount_value', 'max_discount_minor',
        'scope', 'starts_at', 'ends_at',
        'priority', 'is_active', 'is_featured',
        'badge_text', 'sort_order',
    ];

    protected $casts = [
        'discount_type'        => PromotionDiscountType::class,
        'scope'              => PromotionScope::class,
        'max_discount_minor' => MoneyCast::class,
        'starts_at'          => 'datetime',
        'ends_at'            => 'datetime',
        'is_active'          => 'boolean',
        'is_featured'        => 'boolean',
        'priority'           => 'integer',
        'sort_order'         => 'integer',
    ];

    protected static function booted(): void
    {
        $forget = fn () => Cache::forget('promotions.active');

        static::saved(function (Promotion $promo) use ($forget) {
            $forget();

            if (! $promo->wasChanged('scope')) {
                return;
            }

            $allowed = match ($promo->scope) {
                PromotionScope::Category => [Category::class],
                PromotionScope::Product  => [Product::class],
                PromotionScope::Variant  => [ProductVariant::class],
                PromotionScope::All      => [],
            };

            if ($allowed === []) {
                $promo->targets()->delete();
            } else {
                $promo->targets()->whereNotIn('target_type', $allowed)->delete();
            }
        });

        static::deleted($forget);
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('banner')->singleFile();
    }

    public function scopeActive(Builder $q): Builder
    {
        return $q->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function scopeFeatured(Builder $q): Builder
    {
        return $q->where('is_featured', true);
    }

    public function targets(): HasMany
    {
        return $this->hasMany(PromotionTarget::class);
    }

    public function categories(): MorphToMany
    {
        return $this->morphedByMany(Category::class, 'target', 'promotion_targets');
    }

    public function products(): MorphToMany
    {
        return $this->morphedByMany(Product::class, 'target', 'promotion_targets');
    }

    public function variants(): MorphToMany
    {
        return $this->morphedByMany(ProductVariant::class, 'target', 'promotion_targets');
    }

    public function isRunning(): bool
    {
        if (! $this->is_active) {
            return false;
        }

        if ($this->starts_at && now()->lt($this->starts_at)) {
            return false;
        }

        if ($this->ends_at && now()->gt($this->ends_at)) {
            return false;
        }

        return true;
    }

    public function daysRemaining(): ?int
    {
        if (! $this->ends_at) {
            return null;
        }

        $days = (int) now()->diffInDays($this->ends_at, false);

        return $days >= 0 ? $days : null;
    }

    public function appliesTo(ProductVariant $variant): bool
    {
        if (! $this->isRunning()) {
            return false;
        }

        return match ($this->scope) {
            PromotionScope::All      => true,
            PromotionScope::Variant  => $this->targets()
                ->where('target_type', ProductVariant::class)
                ->where('target_id', $variant->id)
                ->exists(),
            PromotionScope::Product  => $this->targets()
                ->where('target_type', Product::class)
                ->where('target_id', $variant->product_id)
                ->exists(),
            PromotionScope::Category => $this->appliesToCategory($variant),
        };
    }

    private function appliesToCategory(ProductVariant $variant): bool
    {
        $categoryId = $variant->product?->category_id;

        if (! $categoryId) {
            return false;
        }

        $targetIds = $this->relationLoaded('targets')
            ? $this->targets->where('target_type', Category::class)->pluck('target_id')
            : $this->targets()->where('target_type', Category::class)->pluck('target_id');

        if ($targetIds->isEmpty()) {
            return false;
        }

        foreach ($targetIds as $targetId) {
            $exists = Category::where('id', $targetId)
                ->whereDescendantOrSelf($categoryId)
                ->exists();

            if ($exists) {
                return true;
            }
        }

        return false;
    }

    public function applyTo(Money $originalPrice): Money
    {
        $result = match ($this->discount_type) {
            PromotionDiscountType::Percent => $this->applyPercent($originalPrice),
            PromotionDiscountType::FixedAmount => $originalPrice
                ->minus(Money::ofMinor((int) $this->discount_value)),
            PromotionDiscountType::FixedPrice => Money::ofMinor((int) $this->discount_value),
        };

        return $result->clampToZero();
    }

    private function applyPercent(Money $originalPrice): Money
    {
        $discount = $originalPrice->multipliedBy($this->discount_value / 10000);

        if ($this->max_discount_minor && $this->max_discount_minor->isPositive()) {
            $discount = $discount->min($this->max_discount_minor);
        }

        return $originalPrice->minus($discount);
    }

    public function discountPercent(Money $original): float
    {
        if ($original->isZero()) {
            return 0.0;
        }

        $saved = $original->minus($this->applyTo($original))->minor;

        return round($saved / $original->minor * 100, 1);
    }

    public function discountLabel(): string
    {
        return match ($this->discount_type) {
            PromotionDiscountType::Percent => number_format($this->discount_value / 100, 1) . '%',
            PromotionDiscountType::FixedAmount,
            PromotionDiscountType::FixedPrice => Money::ofMinor((int) $this->discount_value)->format(),
        };
    }

    public function statusLabel(): string
    {
        if (! $this->is_active) {
            return 'موقوف';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'مجدول';
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return 'منتهٍ';
        }

        return 'جارٍ الآن';
    }

    public function statusColor(): string
    {
        return match ($this->statusLabel()) {
            'جارٍ الآن' => 'success',
            'مجدول'     => 'warning',
            'منتهٍ'     => 'danger',
            default     => 'gray',
        };
    }
}
