<?php

namespace App\Models;

use App\Enums\BillingCycle;
use App\Enums\FeatureAddon;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSubscription extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'plan',
        'status',
        'billing_cycle',
        'current_period_start',
        'current_period_end',
        'cancelled_at',
        'stripe_subscription_id',
    ];

    protected function casts(): array
    {
        return [
            'plan' => SubscriptionPlan::class,
            'status' => SubscriptionStatus::class,
            'billing_cycle' => BillingCycle::class,
            'current_period_start' => 'datetime',
            'current_period_end' => 'datetime',
            'cancelled_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function isAccessible(): bool
    {
        return $this->status->isAccessible();
    }

    public function hasFeature(FeatureAddon $feature): bool
    {
        return $this->isAccessible() && $this->plan->hasFeature($feature);
    }

    public function isTrialing(): bool
    {
        return $this->status === SubscriptionStatus::Trialing;
    }

    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::Active;
    }

    public function isCancelled(): bool
    {
        return $this->status === SubscriptionStatus::Cancelled;
    }

    public function isPastDue(): bool
    {
        return $this->status === SubscriptionStatus::PastDue;
    }

    public function onGracePeriod(): bool
    {
        return $this->cancelled_at !== null
            && $this->current_period_end !== null
            && $this->current_period_end->isFuture();
    }
}
