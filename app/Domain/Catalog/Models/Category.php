<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    ];

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

    public function scopeActive($q)   { return $q->where('is_active', true); }
    public function scopeFeatured($q) { return $q->where('is_featured', true); }

    /** مسار كامل: "بقالة › حبوب › أرز" */
    public function getBreadcrumbAttribute(): string
    {
        return static::ancestorsAndSelf($this->getKey())->pluck('name')->implode(' › ');
    }
}
