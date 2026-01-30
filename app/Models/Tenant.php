<?php

namespace App\Models;

use App\Enums\FeatureAddon;
use App\Enums\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Tenant extends Model
{
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'email',
        'phone',
        'address',
        'logo_path',
        'settings',
        'is_active',
        'trial_ends_at',
    ];

    protected function casts(): array
    {
        return [
            'settings' => 'array',
            'is_active' => 'boolean',
            'trial_ends_at' => 'datetime',
        ];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function locations(): HasMany
    {
        return $this->hasMany(Location::class);
    }

    public function departments(): HasMany
    {
        return $this->hasMany(Department::class);
    }

    public function businessRoles(): HasMany
    {
        return $this->hasMany(BusinessRole::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function leaveTypes(): HasMany
    {
        return $this->hasMany(LeaveType::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function tenantSettings(): HasOne
    {
        return $this->hasOne(TenantSettings::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(TenantSubscription::class);
    }

    public function featureAddons(): HasMany
    {
        return $this->hasMany(TenantFeatureAddon::class);
    }

    public function activeFeatureAddons(): HasMany
    {
        return $this->featureAddons()->active();
    }

    /**
     * Check if the tenant has access to a specific feature.
     * A feature is accessible if it's included in the plan OR purchased as an add-on.
     */
    public function hasFeature(FeatureAddon $feature): bool
    {
        // Check if the subscription includes this feature
        if ($this->subscription?->hasFeature($feature)) {
            return true;
        }

        // Check if the tenant has an active add-on for this feature
        return $this->activeFeatureAddons()
            ->where('feature', $feature)
            ->exists();
    }

    /**
     * Check if the tenant has a specific subscription plan or higher.
     */
    public function hasPlan(SubscriptionPlan $plan): bool
    {
        if (! $this->subscription?->isAccessible()) {
            return false;
        }

        return $this->subscription->plan->order() >= $plan->order();
    }

    /**
     * Get the tenant's current subscription plan.
     */
    public function getPlan(): ?SubscriptionPlan
    {
        return $this->subscription?->plan;
    }

    // Convenience methods for checking specific features

    public function hasAIScheduling(): bool
    {
        return $this->hasFeature(FeatureAddon::AiScheduling);
    }

    public function hasAdvancedAnalytics(): bool
    {
        return $this->hasFeature(FeatureAddon::AdvancedAnalytics);
    }

    public function hasApiAccess(): bool
    {
        return $this->hasFeature(FeatureAddon::ApiAccess);
    }

    public function hasPrioritySupport(): bool
    {
        return $this->hasFeature(FeatureAddon::PrioritySupport);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOnTrial($query)
    {
        return $query->whereNotNull('trial_ends_at')
            ->where('trial_ends_at', '>', now());
    }

    public function isOnTrial(): bool
    {
        return $this->trial_ends_at && $this->trial_ends_at->isFuture();
    }

    public function getSetting(string $key, mixed $default = null): mixed
    {
        return data_get($this->settings, $key, $default);
    }

    public function setSetting(string $key, mixed $value): void
    {
        $settings = $this->settings ?? [];
        data_set($settings, $key, $value);
        $this->settings = $settings;
    }
}
