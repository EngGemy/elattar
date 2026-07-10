<?php

declare(strict_types=1);

namespace App\Domain\Crm\Models;

use App\Domain\Sales\Models\Order;
use App\Domain\Shared\ValueObjects\Money;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Customer extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = ['user_id', 'name', 'phone', 'email', 'group', 'notes', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function user(): BelongsTo      { return $this->belongsTo(User::class); }
    public function addresses(): HasMany   { return $this->hasMany(Address::class); }
    public function orders(): HasMany      { return $this->hasMany(Order::class); }

    public function defaultAddress(): HasOne
    {
        return $this->hasOne(Address::class)->where('is_default', true);
    }

    /** إجمالي قيمة العميل مدى الحياة (CLV) */
    public function lifetimeValue(): Money
    {
        return Money::ofMinor((int) $this->orders()->revenueGenerating()->sum('total_minor'));
    }

    /** متوسط قيمة الطلب (AOV) */
    public function averageOrderValue(): Money
    {
        $count = $this->orders()->revenueGenerating()->count();

        return $count === 0
            ? Money::zero()
            : Money::ofMinor((int) round($this->lifetimeValue()->minor / $count));
    }

    public function ordersCount(): int
    {
        return $this->orders()->revenueGenerating()->count();
    }

    /** عميل متكرر = أكثر من طلب واحد */
    public function isRepeat(): bool
    {
        return $this->ordersCount() > 1;
    }
}
