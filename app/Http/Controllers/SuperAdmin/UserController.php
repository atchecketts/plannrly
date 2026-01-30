<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserRoleAssignment;
use App\Traits\HandlesSorting;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    use HandlesSorting;

    private function getSortableColumns(): array
    {
        return [
            'name' => 'first_name',
            'tenant' => function (Builder $query, string $direction) {
                $query->orderBy(
                    Tenant::select('name')
                        ->whereColumn('tenants.id', 'users.tenant_id')
                        ->limit(1),
                    $direction
                );
            },
            'role' => function (Builder $query, string $direction) {
                $query->orderBy(
                    UserRoleAssignment::select('system_role')
                        ->whereColumn('user_role_assignments.user_id', 'users.id')
                        ->orderByRaw("CASE system_role
                            WHEN 'super_admin' THEN 1
                            WHEN 'admin' THEN 2
                            WHEN 'location_admin' THEN 3
                            WHEN 'department_admin' THEN 4
                            WHEN 'employee' THEN 5
                            ELSE 6 END")
                        ->limit(1),
                    $direction
                );
            },
            'status' => 'is_active',
            'last_login' => 'last_login_at',
        ];
    }

    private const GROUPABLE_COLUMNS = ['name', 'tenant', 'role', 'status', 'last_login'];

    public function index(Request $request): View
    {
        $query = User::query()
            ->with(['tenant', 'roleAssignments'])
            ->withTrashed();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('first_name', 'like', "%{$search}%")
                    ->orWhere('last_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        if ($request->filled('tenant_id')) {
            $query->where('tenant_id', $request->tenant_id);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true)->whereNull('deleted_at');
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false)->whereNull('deleted_at');
            } elseif ($request->status === 'deleted') {
                $query->onlyTrashed();
            }
        }

        $sortableColumns = $this->getSortableColumns();
        $sortParams = $this->getSortParameters($request, $sortableColumns, self::GROUPABLE_COLUMNS);
        $allGroups = $this->getUserGroups($query, $sortParams['group']);

        $this->applySorting($query, $request, $sortableColumns, 'name', 'asc', self::GROUPABLE_COLUMNS);

        // When grouping, fetch all records; otherwise paginate
        if ($sortParams['group']) {
            $users = $query->get();
        } else {
            $users = $query->paginate(20)->withQueryString();
        }

        $tenants = Tenant::orderBy('name')->get(['id', 'name']);

        return view('super-admin.users.index', compact('users', 'tenants', 'sortParams', 'allGroups'));
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
            'tenant' => (clone $query)
                ->reorder()
                ->with('tenant')
                ->get()
                ->pluck('tenant.name')
                ->unique()
                ->filter()
                ->sort()
                ->prepend(null)
                ->unique()
                ->map(fn ($name) => [
                    'key' => 'tenant-'.($name ? \Str::slug($name) : 'no-tenant'),
                    'label' => $name ?? 'No Tenant',
                ])->values()->toArray(),
            'role' => collect([
                ['key' => 'role-super-admin', 'label' => 'Super Admin'],
                ['key' => 'role-admin', 'label' => 'Admin'],
                ['key' => 'role-location-admin', 'label' => 'Location Admin'],
                ['key' => 'role-department-admin', 'label' => 'Department Admin'],
                ['key' => 'role-employee', 'label' => 'Employee'],
            ])->toArray(),
            'status' => $this->getAllGroupValues(
                $query,
                'is_active',
                fn ($isActive) => [
                    'key' => 'status-'.($isActive ? 'active' : 'inactive'),
                    'label' => $isActive ? 'Active' : 'Inactive',
                ]
            )->toArray(),
            'last_login' => $this->getAllGroupValues(
                $query,
                'last_login_at',
                fn ($date) => [
                    'key' => 'last_login-'.($date ? $date->format('Y-m-d') : 'Never'),
                    'label' => $date ? $date->format('M d, Y') : 'Never Logged In',
                ]
            )->unique('key')->values()->toArray(),
            default => [],
        };
    }

    public function show(User $user): View
    {
        $user->load(['tenant', 'roleAssignments.location', 'roleAssignments.department', 'businessRoles.department']);

        return view('super-admin.users.show', compact('user'));
    }
}
