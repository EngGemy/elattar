<?php

declare(strict_types=1);

namespace App\Domain\Crm\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    protected $fillable = [
        'customer_id', 'type', 'label', 'recipient_name', 'phone',
        'governorate', 'city', 'area', 'street', 'building', 'landmark', 'is_default',
    ];

    protected $casts = ['is_default' => 'boolean'];

    public function customer(): BelongsTo { return $this->belongsTo(Customer::class); }

    /** لقطة مجمّدة تُخزَّن في الطلب كـ JSON — لا FK */
    public function toSnapshot(): array
    {
        return $this->only([
            'recipient_name', 'phone', 'governorate', 'city',
            'area', 'street', 'building', 'landmark',
        ]);
    }

    public function getFullAddressAttribute(): string
    {
        return collect([$this->street, $this->building, $this->area, $this->city, $this->governorate])
            ->filter()->implode(' - ');
    }
}
