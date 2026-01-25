<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBusinessRole extends Model
{
    protected $fillable = [
        'user_id',
        'business_role_id',
        'hourly_rate',
        'is_primary',
    ];

    protected function casts(): array
    {
        return [
            'hourly_rate' => 'decimal:2',
            'is_primary' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function businessRole(): BelongsTo
    {
        return $this->belongsTo(BusinessRole::class);
    }

    public function getEffectiveHourlyRateAttribute(): ?float
    {
        return $this->hourly_rate ?? $this->businessRole?->default_hourly_rate;
    }

    public function scopePrimary($query)
    {
        return $query->where('is_primary', true);
    }
}
