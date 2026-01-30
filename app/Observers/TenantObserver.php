<?php

namespace App\Observers;

use App\Enums\BillingCycle;
use App\Enums\SubscriptionPlan;
use App\Enums\SubscriptionStatus;
use App\Models\Tenant;
use App\Models\TenantSettings;
use App\Models\TenantSubscription;

class TenantObserver
{
    public function created(Tenant $tenant): void
    {
        TenantSettings::create([
            'tenant_id' => $tenant->id,
        ]);

        TenantSubscription::create([
            'tenant_id' => $tenant->id,
            'plan' => SubscriptionPlan::Basic,
            'status' => SubscriptionStatus::Trialing,
            'billing_cycle' => BillingCycle::Monthly,
            'current_period_start' => now(),
            'current_period_end' => now()->addDays(14),
        ]);
    }
}
