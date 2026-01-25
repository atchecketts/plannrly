<?php

namespace App\Providers;

use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Location;
use App\Models\Rota;
use App\Models\Shift;
use App\Models\ShiftSwapRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Policies\BusinessRolePolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\LeaveRequestPolicy;
use App\Policies\LocationPolicy;
use App\Policies\RotaPolicy;
use App\Policies\ShiftPolicy;
use App\Policies\ShiftSwapPolicy;
use App\Policies\TenantPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        Gate::policy(Tenant::class, TenantPolicy::class);
        Gate::policy(Location::class, LocationPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(BusinessRole::class, BusinessRolePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Rota::class, RotaPolicy::class);
        Gate::policy(Shift::class, ShiftPolicy::class);
        Gate::policy(LeaveRequest::class, LeaveRequestPolicy::class);
        Gate::policy(ShiftSwapRequest::class, ShiftSwapPolicy::class);
    }
}
