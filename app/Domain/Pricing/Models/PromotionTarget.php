<?php

declare(strict_types=1);

namespace App\Domain\Pricing\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Cache;

class PromotionTarget extends Model
{
    protected $fillable = [
        'promotion_id',
        'target_type',
        'target_id',
    ];

    protected static function booted(): void
    {
        $forget = fn () => Cache::forget('promotions.active');

        static::saved($forget);
        static::deleted($forget);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function target(): MorphTo
    {
        return $this->morphTo();
    }
}
