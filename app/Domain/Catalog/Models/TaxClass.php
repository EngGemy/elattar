<?php

declare(strict_types=1);

namespace App\Domain\Catalog\Models;

use Illuminate\Database\Eloquent\Model;

/** فئة الضريبة — 14% مصر / 15% السعودية / 0% معفى */
class TaxClass extends Model
{
    protected $fillable = ['name', 'code', 'rate', 'is_inclusive', 'is_default'];

    protected $casts = [
        'rate'         => 'decimal:4',
        'is_inclusive' => 'boolean',
        'is_default'   => 'boolean',
    ];

    /** النسبة كنسبة مئوية: 0.14 → 14.0 */
    public function ratePercent(): float
    {
        return (float) $this->rate * 100;
    }

    public static function default(): ?self
    {
        return static::where('is_default', true)->first();
    }
}
