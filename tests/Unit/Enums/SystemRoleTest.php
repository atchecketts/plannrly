<?php

namespace Tests\Unit\Enums;

use App\Enums\SystemRole;
use Tests\TestCase;

class SystemRoleTest extends TestCase
{
    public function test_super_admin_is_admin_level(): void
    {
        $this->assertTrue(SystemRole::SuperAdmin->isAdminLevel());
    }

    public function test_employee_is_not_admin_level(): void
    {
        $this->assertFalse(SystemRole::Employee->isAdminLevel());
    }

    public function test_super_admin_can_manage_locations(): void
    {
        $this->assertTrue(SystemRole::SuperAdmin->canManageLocations());
    }

    public function test_admin_can_manage_locations(): void
    {
        $this->assertTrue(SystemRole::Admin->canManageLocations());
    }

    public function test_location_admin_cannot_manage_locations(): void
    {
        $this->assertFalse(SystemRole::LocationAdmin->canManageLocations());
    }

    public function test_location_admin_can_manage_departments(): void
    {
        $this->assertTrue(SystemRole::LocationAdmin->canManageDepartments());
    }

    public function test_department_admin_cannot_manage_departments(): void
    {
        $this->assertFalse(SystemRole::DepartmentAdmin->canManageDepartments());
    }

    public function test_department_admin_can_manage_business_roles(): void
    {
        $this->assertTrue(SystemRole::DepartmentAdmin->canManageBusinessRoles());
    }

    public function test_employee_cannot_manage_anything(): void
    {
        $this->assertFalse(SystemRole::Employee->canManageLocations());
        $this->assertFalse(SystemRole::Employee->canManageDepartments());
        $this->assertFalse(SystemRole::Employee->canManageBusinessRoles());
        $this->assertFalse(SystemRole::Employee->canManageUsers());
        $this->assertFalse(SystemRole::Employee->canApproveLeave());
    }

    public function test_label_returns_readable_string(): void
    {
        $this->assertEquals('Super Admin', SystemRole::SuperAdmin->label());
        $this->assertEquals('Admin', SystemRole::Admin->label());
        $this->assertEquals('Location Admin', SystemRole::LocationAdmin->label());
        $this->assertEquals('Department Admin', SystemRole::DepartmentAdmin->label());
        $this->assertEquals('Employee', SystemRole::Employee->label());
    }

    public function test_for_tenant_admins_excludes_super_admin(): void
    {
        $roles = SystemRole::forTenantAdmins();

        $this->assertNotContains(SystemRole::SuperAdmin, $roles);
        $this->assertContains(SystemRole::Admin, $roles);
        $this->assertContains(SystemRole::Employee, $roles);
    }
}
