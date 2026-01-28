<?php

namespace App\Providers;

use App\Enums\FeatureAddon;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\LeaveRequest;
use App\Models\Location;
use App\Models\Shift;
use App\Models\ShiftSwapRequest;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserEmploymentDetails;
use App\Observers\TenantObserver;
use App\Policies\BusinessRolePolicy;
use App\Policies\DepartmentPolicy;
use App\Policies\LeaveRequestPolicy;
use App\Policies\LocationPolicy;
use App\Policies\ShiftPolicy;
use App\Policies\ShiftSwapPolicy;
use App\Policies\TenantPolicy;
use App\Policies\UserEmploymentDetailsPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->ensureDirectoriesExist();
    }

    public function boot(): void
    {
        Tenant::observe(TenantObserver::class);

        Gate::policy(Tenant::class, TenantPolicy::class);
        Gate::policy(Location::class, LocationPolicy::class);
        Gate::policy(Department::class, DepartmentPolicy::class);
        Gate::policy(BusinessRole::class, BusinessRolePolicy::class);
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Shift::class, ShiftPolicy::class);
        Gate::policy(LeaveRequest::class, LeaveRequestPolicy::class);
        Gate::policy(ShiftSwapRequest::class, ShiftSwapPolicy::class);
        Gate::policy(UserEmploymentDetails::class, UserEmploymentDetailsPolicy::class);

        $this->registerBladeDirectives();
    }

    protected function registerBladeDirectives(): void
    {
        Blade::if('feature', function (string $feature) {
            $user = auth()->user();

            if (! $user || ! $user->tenant) {
                return false;
            }

            $featureEnum = FeatureAddon::tryFrom($feature);

            if (! $featureEnum) {
                return false;
            }

            return $user->tenant->hasFeature($featureEnum);
        });
    }

    protected function ensureDirectoriesExist(): void
    {
        $paths = [
            storage_path('framework/cache'),
            storage_path('framework/sessions'),
            storage_path('framework/views'),
        ];

        foreach ($paths as $path) {
            if (! is_dir($path)) {
                mkdir($path, 0755, true);
            }
        }
    }
}
