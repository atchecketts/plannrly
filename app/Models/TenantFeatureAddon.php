<?php

namespace App\Models;

use App\Enums\FeatureAddon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantFeatureAddon extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'feature',
        'enabled_at',
        'expires_at',
        'stripe_subscription_item_id',
    ];

    protected function casts(): array
    {
        return [
            'feature' => FeatureAddon::class,
            'enabled_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isActive(): bool
    {
        if ($this->expires_at === null) {
            return true;
        }

        return $this->expires_at->isFuture();
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }
}
