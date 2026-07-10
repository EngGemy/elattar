<?php

declare(strict_types=1);

namespace App\Domain\Sales\Models;

use App\Casts\MoneyCast;
use App\Domain\Catalog\Models\ProductVariant;
use App\Domain\Shared\Enums\UnitOfMeasure;
use App\Domain\Shared\ValueObjects\Money;
use App\Domain\Shared\ValueObjects\Quantity;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * سطر الفاتورة.
 *
 * ⚠ cost_minor مُجمَّد لحظة البيع.
 * لو حسبت الربح من variants.cost وقت التقرير، وتغيّرت التكلفة،
 * فكل أرباحك التاريخية خطأ. هذا الفرق بين نظام محاسبي ونظام هاوٍ.
 */
class OrderLine extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'order_id', 'variant_id', 'sku_snapshot', 'name_snapshot', 'attributes_snapshot',
        'qty', 'unit', 'unit_price_minor', 'cost_minor',
        'line_discount_minor', 'tax_rate', 'tax_minor', 'line_total_minor',
    ];

    protected $casts = [
        'attributes_snapshot' => 'array',
        'qty'                 => 'decimal:3',
        'unit'                => UnitOfMeasure::class,
        'unit_price_minor'    => MoneyCast::class,
        'cost_minor'          => MoneyCast::class,
        'line_discount_minor' => MoneyCast::class,
        'tax_rate'            => 'decimal:4',
        'tax_minor'           => MoneyCast::class,
        'line_total_minor'    => MoneyCast::class,
    ];

    protected $hidden = ['cost_minor'];

    public function order(): BelongsTo   { return $this->belongsTo(Order::class); }
    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class, 'variant_id'); }

    public function quantity(): Quantity
    {
        return Quantity::of((float) $this->qty, $this->unit);
    }

    /** تكلفة السطر = التكلفة/الوحدة × الكمية (بنفس منطق التسعير) */
    public function lineCost(): Money
    {
        $factor = in_array($this->unit, [UnitOfMeasure::Gram, UnitOfMeasure::Ml], true)
            ? (float) $this->qty / 1000
            : (float) $this->qty;

        return $this->cost_minor->multipliedBy($factor);
    }

    public function lineProfit(): Money
    {
        return $this->line_total_minor
            ->minus($this->tax_minor)
            ->minus($this->lineCost());
    }
}
