<?php

declare(strict_types=1);

namespace App\Domain\Pricing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ShippingZone extends Model
{
    protected $fillable = ['name', 'cities', 'is_active'];
    protected $casts    = ['cities' => 'array', 'is_active' => 'boolean'];

    public function methods(): HasMany { return $this->hasMany(ShippingMethod::class); }

    public function coversCity(string $city): bool
    {
        return in_array($city, $this->cities ?? [], true);
    }
}
