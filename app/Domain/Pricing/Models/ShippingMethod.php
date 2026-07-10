<?php

declare(strict_types=1);

namespace App\Domain\Pricing\Models;

use App\Casts\MoneyCast;
use App\Domain\Shared\ValueObjects\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShippingMethod extends Model
{
    protected $fillable = [
        'shipping_zone_id', 'name', 'rate_type',
        'base_rate_minor', 'per_kg_minor', 'free_above_minor', 'eta_hours', 'is_active',
    ];

    protected $casts = [
        'base_rate_minor'  => MoneyCast::class,
        'per_kg_minor'     => MoneyCast::class,
        'free_above_minor' => MoneyCast::class,
        'is_active'        => 'boolean',
    ];

    public function zone(): BelongsTo { return $this->belongsTo(ShippingZone::class, 'shipping_zone_id'); }

    /** حساب أجرة الشحن حسب المبلغ والوزن الإجمالي */
    public function calculate(Money $subtotal, float $totalWeightKg = 0): Money
    {
        // شحن مجاني فوق حد معيّن
        if ($this->free_above_minor
            && $this->free_above_minor->isPositive()
            && ! $subtotal->lessThan($this->free_above_minor)) {
            return Money::zero();
        }

        return match ($this->rate_type) {
            'free'      => Money::zero(),
            'flat'      => $this->base_rate_minor,
            'by_weight' => $this->base_rate_minor->plus($this->per_kg_minor->multipliedBy(ceil($totalWeightKg))),
        };
    }
}
