<?php

declare(strict_types=1);

namespace App\Domain\Pricing\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CouponUsage extends Model
{
    public $timestamps = false;

    protected $fillable = ['coupon_id', 'customer_id', 'order_id', 'discount_minor'];
    protected $casts    = ['discount_minor' => MoneyCast::class, 'created_at' => 'datetime'];

    public function coupon(): BelongsTo { return $this->belongsTo(Coupon::class); }
}
