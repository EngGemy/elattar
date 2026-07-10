<?php

declare(strict_types=1);

namespace App\Domain\Pos\Models;

use App\Domain\Inventory\Models\Warehouse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Register extends Model
{
    protected $fillable = ['warehouse_id', 'code', 'name', 'is_active'];
    protected $casts    = ['is_active' => 'boolean'];

    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function sessions(): HasMany    { return $this->hasMany(RegisterSession::class); }

    public function openSession(): ?RegisterSession
    {
        return $this->sessions()->where('status', 'open')->first();
    }

    public function hasOpenSession(): bool
    {
        return $this->openSession() !== null;
    }
}
