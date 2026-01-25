<?php

namespace App\Http\Controllers\Auth;

use App\Enums\SystemRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\LeaveType;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(): View
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $result = DB::transaction(function () use ($data) {
            $tenant = Tenant::create([
                'name' => $data['company_name'],
                'slug' => Str::slug($data['company_name']) . '-' . Str::random(6),
                'email' => $data['email'],
                'settings' => [
                    'timezone' => 'UTC',
                    'date_format' => 'Y-m-d',
                    'time_format' => 'H:i',
                ],
                'is_active' => true,
                'trial_ends_at' => now()->addDays(14),
            ]);

            $user = User::create([
                'tenant_id' => $tenant->id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'is_active' => true,
                'email_verified_at' => now(),
            ]);

            UserRoleAssignment::create([
                'user_id' => $user->id,
                'system_role' => SystemRole::Admin->value,
            ]);

            $this->copyDefaultLeaveTypes($tenant);

            return $user;
        });

        Auth::login($result);

        return redirect()->route('dashboard');
    }

    protected function copyDefaultLeaveTypes(Tenant $tenant): void
    {
        $defaults = LeaveType::whereNull('tenant_id')->get();

        foreach ($defaults as $default) {
            LeaveType::create([
                'tenant_id' => $tenant->id,
                'name' => $default->name,
                'color' => $default->color,
                'requires_approval' => $default->requires_approval,
                'affects_allowance' => $default->affects_allowance,
                'is_paid' => $default->is_paid,
                'is_active' => true,
            ]);
        }
    }
}
