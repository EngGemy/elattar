<?php

declare(strict_types=1);

namespace App\Domain\Sales\Models;

use App\Casts\MoneyCast;
use App\Domain\Shared\Concerns\GeneratesDocumentNumber;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use GeneratesDocumentNumber;

    protected $fillable = ['number', 'order_id', 'total_minor', 'pdf_path', 'issued_at'];
    protected $casts    = ['total_minor' => MoneyCast::class, 'issued_at' => 'datetime'];

    public static function documentPrefix(): string { return 'INV'; }

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
