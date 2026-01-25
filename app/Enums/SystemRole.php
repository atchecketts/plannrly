<?php

namespace App\Enums;

enum SystemRole: string
{
    case SuperAdmin = 'super_admin';
    case Admin = 'admin';
    case LocationAdmin = 'location_admin';
    case DepartmentAdmin = 'department_admin';
    case Employee = 'employee';

    public function label(): string
    {
        return match ($this) {
            self::SuperAdmin => 'Super Admin',
            self::Admin => 'Admin',
            self::LocationAdmin => 'Location Admin',
            self::DepartmentAdmin => 'Department Admin',
            self::Employee => 'Employee',
        };
    }

    public function isAdminLevel(): bool
    {
        return in_array($this, [
            self::SuperAdmin,
            self::Admin,
            self::LocationAdmin,
            self::DepartmentAdmin,
        ]);
    }

    public function canManageLocations(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin]);
    }

    public function canManageDepartments(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin, self::LocationAdmin]);
    }

    public function canManageBusinessRoles(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin, self::LocationAdmin, self::DepartmentAdmin]);
    }

    public function canManageUsers(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin, self::LocationAdmin, self::DepartmentAdmin]);
    }

    public function canApproveLeave(): bool
    {
        return in_array($this, [self::SuperAdmin, self::Admin, self::LocationAdmin, self::DepartmentAdmin]);
    }

    public static function forTenantAdmins(): array
    {
        return [
            self::Admin,
            self::LocationAdmin,
            self::DepartmentAdmin,
            self::Employee,
        ];
    }
}
