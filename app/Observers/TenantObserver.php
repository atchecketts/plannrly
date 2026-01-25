<?php

namespace App\Observers;

use App\Models\Tenant;
use App\Models\TenantSettings;

class TenantObserver
{
    public function created(Tenant $tenant): void
    {
        TenantSettings::create([
            'tenant_id' => $tenant->id,
        ]);
    }
}
