<?php

declare(strict_types=1);

namespace App\Domain\Inventory\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Warehouse extends Model
{
    protected $fillable = ['code', 'name', 'type', 'address', 'phone', 'is_default', 'is_active'];

    protected $casts = ['is_default' => 'boolean', 'is_active' => 'boolean'];

    public function stockLevels(): HasMany { return $this->hasMany(StockLevel::class); }
    public function movements(): HasMany   { return $this->hasMany(StockMovement::class); }

    public static function default(): self
    {
        return static::where('is_default', true)->firstOrFail();
    }

    public function scopeActive($q) { return $q->where('is_active', true); }
}
