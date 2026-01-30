<?php

namespace App\Http\Controllers;

use App\Enums\SystemRole;
use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\User;
use App\Models\UserBusinessRole;
use App\Models\UserRoleAssignment;
use App\Traits\HandlesSorting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class UserController extends Controller
{
    use HandlesSorting;

    private const SORTABLE_COLUMNS = [
        'name' => 'first_name',
        'email' => 'email',
        'status' => 'is_active',
    ];

    private const GROUPABLE_COLUMNS = ['name', 'email', 'status'];

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);

        $query = User::with(['roleAssignments', 'businessRoles'])
            ->where('tenant_id', auth()->user()->tenant_id);

        $sortParams = $this->getSortParameters($request, self::SORTABLE_COLUMNS, self::GROUPABLE_COLUMNS);

        $allGroups = $this->getUserGroups($query, $sortParams['group']);

        $this->applySorting($query, $request, self::SORTABLE_COLUMNS, 'name', 'asc', self::GROUPABLE_COLUMNS);

        // When grouping, fetch all records; otherwise paginate
        if ($sortParams['group']) {
            $users = $query->get();
        } else {
            $users = $query->paginate(15)->withQueryString();
        }

        return view('users.index', compact('users', 'sortParams', 'allGroups'));
    }

    /**
     * Get all unique group values for users.
     */
    private function getUserGroups($query, ?string $group): array
    {
        if (! $group) {
            return [];
        }

        return match ($group) {
            'name' => $this->getAllGroupValues(
                $query,
                'first_name',
                fn ($name) => [
                    'key' => 'name-'.strtoupper(substr($name, 0, 1)),
                    'label' => strtoupper(substr($name, 0, 1)),
                ]
            )->unique('key')->values()->toArray(),
            'email' => $this->getAllGroupValues(
                $query,
                'email',
                fn ($email) => [
                    'key' => 'email-'.\Str::slug(substr(strrchr($email, '@'), 1)),
                    'label' => '@'.substr(strrchr($email, '@'), 1),
                ]
            )->unique('key')->values()->toArray(),
            'status' => $this->getAllGroupValues(
                $query,
                'is_active',
                fn ($isActive) => [
                    'key' => 'status-'.($isActive ? 'active' : 'inactive'),
                    'label' => $isActive ? 'Active' : 'Inactive',
                ]
            )->toArray(),
            default => [],
        };
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        $locations = Location::active()->orderBy('name')->get();
        $departments = Department::with('location')->active()->orderBy('name')->get();
        $businessRoles = BusinessRole::with('department')->active()->orderBy('name')->get();
        $systemRoles = SystemRole::forTenantAdmins();

        return view('users.create', compact('locations', 'departments', 'businessRoles', 'systemRoles'));
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        $data = $request->validated();

        $user = DB::transaction(function () use ($data) {
            $user = User::create([
                'tenant_id' => auth()->user()->tenant_id,
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'password' => $data['password'],
                'is_active' => $data['is_active'] ?? true,
                'email_verified_at' => now(),
            ]);

            UserRoleAssignment::create([
                'user_id' => $user->id,
                'system_role' => $data['system_role'] ?? SystemRole::Employee->value,
                'location_id' => $data['location_id'] ?? null,
                'department_id' => $data['department_id'] ?? null,
                'assigned_by' => auth()->id(),
            ]);

            if (! empty($data['business_role_ids'])) {
                foreach ($data['business_role_ids'] as $roleId) {
                    UserBusinessRole::create([
                        'user_id' => $user->id,
                        'business_role_id' => $roleId,
                        'is_primary' => $roleId == ($data['primary_business_role_id'] ?? $data['business_role_ids'][0]),
                    ]);
                }
            }

            return $user;
        });

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'User created successfully.');
    }

    public function show(User $user): View
    {
        $this->authorize('view', $user);

        $user->load(['roleAssignments.location', 'roleAssignments.department', 'businessRoles.department', 'employmentDetails']);

        return view('users.show', compact('user'));
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        $locations = Location::active()->orderBy('name')->get();
        $departments = Department::with('location')->active()->orderBy('name')->get();
        $businessRoles = BusinessRole::with('department')->active()->orderBy('name')->get();
        $systemRoles = SystemRole::forTenantAdmins();

        $user->load(['roleAssignments', 'businessRoles']);

        return view('users.edit', compact('user', 'locations', 'departments', 'businessRoles', 'systemRoles'));
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();

        DB::transaction(function () use ($data, $user) {
            $userData = [
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'email' => $data['email'],
                'phone' => $data['phone'] ?? null,
                'is_active' => $data['is_active'] ?? true,
            ];

            if (! empty($data['password'])) {
                $userData['password'] = $data['password'];
            }

            $user->update($userData);

            if (isset($data['system_role'])) {
                $user->roleAssignments()->delete();
                UserRoleAssignment::create([
                    'user_id' => $user->id,
                    'system_role' => $data['system_role'],
                    'location_id' => $data['location_id'] ?? null,
                    'department_id' => $data['department_id'] ?? null,
                    'assigned_by' => auth()->id(),
                ]);
            }

            if (isset($data['business_role_ids'])) {
                $user->userBusinessRoles()->delete();
                foreach ($data['business_role_ids'] as $roleId) {
                    UserBusinessRole::create([
                        'user_id' => $user->id,
                        'business_role_id' => $roleId,
                        'is_primary' => $roleId == ($data['primary_business_role_id'] ?? $data['business_role_ids'][0]),
                    ]);
                }
            }
        });

        return redirect()
            ->route('users.show', $user)
            ->with('success', 'User updated successfully.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);

        $user->delete();

        return redirect()
            ->route('users.index')
            ->with('success', 'User deleted successfully.');
    }
}
