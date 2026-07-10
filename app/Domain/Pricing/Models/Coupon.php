<?php

declare(strict_types=1);

namespace App\Domain\Pricing\Models;

use App\Casts\MoneyCast;
use App\Domain\Shared\ValueObjects\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Coupon extends Model
{
    protected $fillable = [
        'code', 'name', 'type', 'value',
        'min_order_minor', 'max_discount_minor',
        'usage_limit', 'usage_limit_per_customer', 'used_count',
        'starts_at', 'ends_at', 'is_active',
    ];

    protected $casts = [
        'value'              => 'decimal:4',
        'min_order_minor'    => MoneyCast::class,
        'max_discount_minor' => MoneyCast::class,
        'starts_at'          => 'datetime',
        'ends_at'            => 'datetime',
        'is_active'          => 'boolean',
    ];

    public function usages(): HasMany { return $this->hasMany(CouponUsage::class); }

    /** هل الكوبون صالح الآن لهذا العميل وهذا المبلغ؟ */
    public function isValidFor(Money $subtotal, ?int $customerId = null): bool
    {
        if (! $this->is_active) return false;
        if ($this->starts_at && now()->lt($this->starts_at)) return false;
        if ($this->ends_at   && now()->gt($this->ends_at))   return false;
        if ($this->usage_limit && $this->used_count >= $this->usage_limit) return false;
        if ($subtotal->lessThan($this->min_order_minor)) return false;

        if ($customerId) {
            $used = $this->usages()->where('customer_id', $customerId)->count();
            if ($used >= $this->usage_limit_per_customer) return false;
        }

        return true;
    }

    /** حساب قيمة الخصم مع تطبيق السقف */
    public function discountFor(Money $subtotal): Money
    {
        $discount = match ($this->type) {
            'percent'       => $subtotal->percentage((float) $this->value),
            'fixed'         => Money::ofMinor((int) $this->value),
            'free_shipping' => Money::zero(),
        };

        if ($this->max_discount_minor && $this->max_discount_minor->isPositive()) {
            $discount = $discount->min($this->max_discount_minor);
        }

        // الخصم لا يتجاوز الإجمالي أبدًا
        return $discount->min($subtotal);
    }
}
