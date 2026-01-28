<?php

namespace App\Models;

use App\Enums\SystemRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory;
    use Notifiable;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'first_name',
        'last_name',
        'email',
        'phone',
        'password',
        'avatar_path',
        'is_active',
        'last_login_at',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

    public function roleAssignments(): HasMany
    {
        return $this->hasMany(UserRoleAssignment::class);
    }

    public function businessRoles(): BelongsToMany
    {
        return $this->belongsToMany(BusinessRole::class, 'user_business_roles')
            ->withPivot(['hourly_rate', 'is_primary'])
            ->withTimestamps();
    }

    public function userBusinessRoles(): HasMany
    {
        return $this->hasMany(UserBusinessRole::class);
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function timeEntries(): HasMany
    {
        return $this->hasMany(TimeEntry::class);
    }

    public function leaveRequests(): HasMany
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function leaveAllowances(): HasMany
    {
        return $this->hasMany(LeaveAllowance::class);
    }

    public function notificationPreferences(): HasMany
    {
        return $this->hasMany(NotificationPreference::class);
    }

    public function filterDefaults(): HasMany
    {
        return $this->hasMany(UserFilterDefault::class);
    }

    public function employmentDetails(): HasOne
    {
        return $this->hasOne(UserEmploymentDetails::class);
    }

    public function availability(): HasMany
    {
        return $this->hasMany(UserAvailability::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getInitialsAttribute(): string
    {
        return strtoupper(substr($this->first_name, 0, 1).substr($this->last_name, 0, 1));
    }

    public function hasSystemRole(SystemRole $role, ?int $locationId = null, ?int $departmentId = null): bool
    {
        $query = $this->roleAssignments()->where('system_role', $role->value);

        if ($locationId) {
            $query->where(function ($q) use ($locationId) {
                $q->whereNull('location_id')->orWhere('location_id', $locationId);
            });
        }

        if ($departmentId) {
            $query->where(function ($q) use ($departmentId) {
                $q->whereNull('department_id')->orWhere('department_id', $departmentId);
            });
        }

        return $query->exists();
    }

    public function isSuperAdmin(): bool
    {
        return $this->hasSystemRole(SystemRole::SuperAdmin);
    }

    public function isAdmin(): bool
    {
        return $this->hasSystemRole(SystemRole::Admin);
    }

    public function isLocationAdmin(?int $locationId = null): bool
    {
        return $this->hasSystemRole(SystemRole::LocationAdmin, $locationId);
    }

    public function isDepartmentAdmin(?int $departmentId = null): bool
    {
        return $this->hasSystemRole(SystemRole::DepartmentAdmin, departmentId: $departmentId);
    }

    public function isEmployee(): bool
    {
        return ! $this->isSuperAdmin()
            && ! $this->isAdmin()
            && ! $this->isLocationAdmin()
            && ! $this->isDepartmentAdmin();
    }

    public function getHighestRole(): ?SystemRole
    {
        $roleOrder = [
            SystemRole::SuperAdmin,
            SystemRole::Admin,
            SystemRole::LocationAdmin,
            SystemRole::DepartmentAdmin,
            SystemRole::Employee,
        ];

        foreach ($roleOrder as $role) {
            if ($this->hasSystemRole($role)) {
                return $role;
            }
        }

        return null;
    }

    public function canManageLocation(Location $location): bool
    {
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return true;
        }

        return $this->isLocationAdmin($location->id);
    }

    public function canManageDepartment(Department $department): bool
    {
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return true;
        }

        if ($this->isLocationAdmin($department->location_id)) {
            return true;
        }

        return $this->isDepartmentAdmin($department->id);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeForTenant($query, int $tenantId)
    {
        return $query->where('tenant_id', $tenantId);
    }

    /**
     * Get all location IDs this user is associated with through their business roles.
     */
    public function getLocationIds(): array
    {
        return $this->businessRoles()
            ->with('department')
            ->get()
            ->pluck('department.location_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Get all department IDs this user is associated with through their business roles.
     */
    public function getDepartmentIds(): array
    {
        return $this->businessRoles()
            ->pluck('department_id')
            ->filter()
            ->unique()
            ->values()
            ->toArray();
    }

    /**
     * Check if this user belongs to the given location through their business roles.
     */
    public function belongsToLocation(int $locationId): bool
    {
        return in_array($locationId, $this->getLocationIds());
    }

    /**
     * Check if this user belongs to the given department through their business roles.
     */
    public function belongsToDepartment(int $departmentId): bool
    {
        return in_array($departmentId, $this->getDepartmentIds());
    }

    /**
     * Get all location IDs this admin user can manage.
     */
    public function getManagedLocationIds(): array
    {
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return Location::where('tenant_id', $this->tenant_id)->pluck('id')->toArray();
        }

        return $this->roleAssignments()
            ->where('system_role', SystemRole::LocationAdmin->value)
            ->whereNotNull('location_id')
            ->pluck('location_id')
            ->toArray();
    }

    /**
     * Get all department IDs this admin user can manage.
     */
    public function getManagedDepartmentIds(): array
    {
        if ($this->isSuperAdmin() || $this->isAdmin()) {
            return Department::where('tenant_id', $this->tenant_id)->pluck('id')->toArray();
        }

        $departmentIds = [];

        // LocationAdmins can manage all departments in their locations
        $locationIds = $this->getManagedLocationIds();
        if (! empty($locationIds)) {
            $departmentIds = Department::whereIn('location_id', $locationIds)->pluck('id')->toArray();
        }

        // DepartmentAdmins can manage their specific departments
        $directDepartmentIds = $this->roleAssignments()
            ->where('system_role', SystemRole::DepartmentAdmin->value)
            ->whereNotNull('department_id')
            ->pluck('department_id')
            ->toArray();

        return array_unique(array_merge($departmentIds, $directDepartmentIds));
    }

    /**
     * Check if this admin user can manage the given user.
     */
    public function canManageUser(User $targetUser): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }

        if ($this->tenant_id !== $targetUser->tenant_id) {
            return false;
        }

        if ($this->isAdmin()) {
            return true;
        }

        // LocationAdmin can manage users in their locations
        if ($this->isLocationAdmin()) {
            $managedLocationIds = $this->getManagedLocationIds();
            $userLocationIds = $targetUser->getLocationIds();

            // User must have at least one business role in a location the admin manages
            return ! empty(array_intersect($managedLocationIds, $userLocationIds));
        }

        // DepartmentAdmin can manage users in their departments
        if ($this->isDepartmentAdmin()) {
            $managedDepartmentIds = $this->getManagedDepartmentIds();
            $userDepartmentIds = $targetUser->getDepartmentIds();

            // User must have at least one business role in a department the admin manages
            return ! empty(array_intersect($managedDepartmentIds, $userDepartmentIds));
        }

        return false;
    }
}
