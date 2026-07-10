<?php

declare(strict_types=1);

namespace App\Domain\Purchasing\Models;

use App\Domain\Shared\ValueObjects\Money;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Supplier extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'code', 'name', 'contact_person', 'phone', 'email',
        'address', 'tax_number', 'payment_terms_days', 'is_active', 'notes',
    ];

    protected $casts = ['is_active' => 'boolean'];

    public function purchaseOrders(): HasMany { return $this->hasMany(PurchaseOrder::class); }

    /** الرصيد المستحق للمورد */
    public function outstandingBalance(): Money
    {
        $total = (int) $this->purchaseOrders()->whereIn('status', ['sent', 'partially_received', 'received'])->sum('total_minor');
        $paid  = (int) $this->purchaseOrders()->sum('paid_minor');

        return Money::ofMinor($total - $paid);
    }
}
