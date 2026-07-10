<?php

declare(strict_types=1);

namespace App\Domain\Sales\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** الخط الزمني لتغيّر حالة الطلب */
class OrderStatusHistory extends Model
{
    public $timestamps = false;
    protected $table = 'order_status_history';

    protected $fillable = ['order_id', 'from_status', 'to_status', 'note', 'user_id'];
    protected $casts    = ['created_at' => 'datetime'];

    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
    public function user(): BelongsTo  { return $this->belongsTo(User::class); }
}
