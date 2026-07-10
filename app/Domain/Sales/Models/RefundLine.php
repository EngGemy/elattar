<?php

declare(strict_types=1);

namespace App\Domain\Sales\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefundLine extends Model
{
    public $timestamps = false;

    protected $fillable = ['refund_id', 'order_line_id', 'qty', 'amount_minor'];
    protected $casts    = ['qty' => 'decimal:3', 'amount_minor' => MoneyCast::class];

    public function refund(): BelongsTo    { return $this->belongsTo(Refund::class); }
    public function orderLine(): BelongsTo { return $this->belongsTo(OrderLine::class); }
}
