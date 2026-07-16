<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Kalnoy\Nestedset\NodeTrait;
use OwenIt\Auditing\Auditable;
use OwenIt\Auditing\Contracts\Auditable as AuditableContract;

/**
 * التصنيف — شجرة متداخلة (Nested Set).
 * جلب الشجرة كاملة = استعلام واحد، لا recursion.
 */
class Category extends Model implements AuditableContract
{
    use HasFactory, SoftDeletes, NodeTrait, Auditable;

    protected $fillable = [
        'parent_id', 'name', 'slug', 'icon', 'image_path',
        'description', 'sort_order', 'is_featured', 'is_active',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_active'   => 'boolean',
        'sort_order'  => 'integer',
        'depth'       => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (Category $category): void {
            if (blank($category->slug)) {
                $category->slug = static::makeUniqueSlug((string) $category->name);
            }
        });

        static::updating(function (Category $category): void {
            if ($category->isDirty('name') && blank($category->slug)) {
                $category->slug = static::makeUniqueSlug((string) $category->name, $category->id);
            }
        });
    }

    /**
     * slug آمن للعربية — Str::slug غالباً يعيد فارغ للأسماء العربية فقط.
     */
    public static function makeUniqueSlug(string $name, ?int $ignoreId = null): string
    {
        $base = Str::slug($name, '-', 'ar');

        if ($base === '' || $base === '-') {
            $base = (string) preg_replace('/\s+/u', '-', trim($name));
            $base = (string) preg_replace('/[^\p{L}\p{N}\-]+/u', '', $base);
            $base = trim($base, '-') ?: 'category';
        }

        $base = mb_strtolower($base);
        $slug = $base;
        $i    = 1;

        while (
            static::withTrashed()
                ->when($ignoreId, fn ($q) => $q->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $base . '-' . $i++;
        }

        return $slug;
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    /** معرّفات هذا التصنيف وجميع أبنائه (للفلترة في الكتالوج) */
    public function descendantIdsIncludingSelf(): \Illuminate\Support\Collection
    {
        return static::descendantsAndSelf($this->getKey())->pluck('id');
    }

    /** كل المنتجات في التصنيف وأحفاده — استعلام واحد */
    public function allProducts()
    {
        return Product::whereIn('category_id', $this->descendantIdsIncludingSelf());
    }

    public function scopeActive($q)
    {
        return $q->where('is_active', true);
    }

    public function scopeFeatured($q)
    {
        return $q->where('is_featured', true);
    }

    /** مسار كامل: "بقالة › حبوب › أرز" */
    public function getBreadcrumbAttribute(): string
    {
        return static::ancestorsAndSelf($this->getKey())->pluck('name')->implode(' › ');
    }
}
