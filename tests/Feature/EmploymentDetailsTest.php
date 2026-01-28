<?php

namespace Tests\Feature;

use App\Enums\EmploymentStatus;
use App\Enums\PayType;
use App\Enums\SystemRole;
use App\Models\Tenant;
use App\Models\User;
use App\Models\UserEmploymentDetails;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmploymentDetailsTest extends TestCase
{
    use RefreshDatabase;

    private Tenant $tenant;

    private User $admin;

    private User $employee;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();

        $this->admin = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $this->admin->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $this->employee = User::factory()->create(['tenant_id' => $this->tenant->id]);
        UserRoleAssignment::create([
            'user_id' => $this->employee->id,
            'system_role' => SystemRole::Employee->value,
        ]);
    }

    public function test_admin_can_view_employment_details_form(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('users.employment.edit', $this->employee));

        $response->assertOk();
        $response->assertViewIs('users.employment');
        $response->assertViewHas('user');
    }

    public function test_employee_cannot_view_other_employment_details_form(): void
    {
        $otherEmployee = User::factory()->create(['tenant_id' => $this->tenant->id]);

        $this->actingAs($this->employee);

        $response = $this->get(route('users.employment.edit', $otherEmployee));

        $response->assertStatus(403);
    }

    public function test_admin_can_create_employment_details(): void
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('users.employment.update', $this->employee), [
            'employment_start_date' => '2023-01-15',
            'employment_status' => EmploymentStatus::Active->value,
            'pay_type' => PayType::Hourly->value,
            'base_hourly_rate' => 25.50,
            'currency' => 'GBP',
            'target_hours_per_week' => 40,
            'overtime_eligible' => true,
        ]);

        $response->assertRedirect(route('users.show', $this->employee));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('user_employment_details', [
            'user_id' => $this->employee->id,
            'employment_status' => 'active',
            'pay_type' => 'hourly',
            'base_hourly_rate' => 25.50,
            'currency' => 'GBP',
            'target_hours_per_week' => 40,
            'overtime_eligible' => true,
        ]);
    }

    public function test_admin_can_update_employment_details(): void
    {
        UserEmploymentDetails::factory()->create([
            'user_id' => $this->employee->id,
            'employment_status' => EmploymentStatus::Active,
            'pay_type' => PayType::Hourly,
            'base_hourly_rate' => 20.00,
        ]);

        $this->actingAs($this->admin);

        $response = $this->put(route('users.employment.update', $this->employee), [
            'employment_status' => EmploymentStatus::NoticePeriod->value,
            'pay_type' => PayType::Hourly->value,
            'base_hourly_rate' => 22.00,
            'currency' => 'GBP',
            'final_working_date' => now()->addMonth()->format('Y-m-d'),
        ]);

        $response->assertRedirect(route('users.show', $this->employee));
        $response->assertSessionHas('success');

        $this->employee->refresh();
        $this->assertEquals(EmploymentStatus::NoticePeriod, $this->employee->employmentDetails->employment_status);
        $this->assertEquals(22.00, $this->employee->employmentDetails->base_hourly_rate);
    }

    public function test_employment_status_enum_values_are_valid(): void
    {
        $this->assertEquals('active', EmploymentStatus::Active->value);
        $this->assertEquals('on_leave', EmploymentStatus::OnLeave->value);
        $this->assertEquals('suspended', EmploymentStatus::Suspended->value);
        $this->assertEquals('notice_period', EmploymentStatus::NoticePeriod->value);
        $this->assertEquals('terminated', EmploymentStatus::Terminated->value);
    }

    public function test_pay_type_enum_values_are_valid(): void
    {
        $this->assertEquals('hourly', PayType::Hourly->value);
        $this->assertEquals('salaried', PayType::Salaried->value);
    }

    public function test_hourly_employee_has_effective_hourly_rate(): void
    {
        $details = UserEmploymentDetails::factory()->hourly()->create([
            'user_id' => $this->employee->id,
            'base_hourly_rate' => 25.00,
        ]);

        $this->assertEquals(25.00, $details->effective_hourly_rate);
    }

    public function test_salaried_employee_has_calculated_hourly_rate(): void
    {
        $details = UserEmploymentDetails::factory()->salaried()->create([
            'user_id' => $this->employee->id,
            'annual_salary' => 52000,
            'target_hours_per_week' => 40,
        ]);

        $expectedHourlyRate = round(52000 / (52 * 40), 2);
        $this->assertEquals($expectedHourlyRate, $details->calculated_hourly_rate);
        $this->assertEquals($expectedHourlyRate, $details->effective_hourly_rate);
    }

    public function test_is_on_probation_returns_correct_value(): void
    {
        $details = UserEmploymentDetails::factory()->onProbation()->create([
            'user_id' => $this->employee->id,
        ]);

        $this->assertTrue($details->isOnProbation());

        $details->update(['probation_end_date' => now()->subDay()]);
        $this->assertFalse($details->isOnProbation());
    }

    public function test_is_leaving_soon_returns_correct_value(): void
    {
        $details = UserEmploymentDetails::factory()->leavingSoon()->create([
            'user_id' => $this->employee->id,
        ]);

        $this->assertTrue($details->isLeavingSoon());

        $details->update(['final_working_date' => now()->addMonths(2)]);
        $this->assertFalse($details->isLeavingSoon(30));
    }

    public function test_validation_requires_employment_status(): void
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('users.employment.update', $this->employee), [
            'pay_type' => PayType::Hourly->value,
            'currency' => 'GBP',
        ]);

        $response->assertSessionHasErrors('employment_status');
    }

    public function test_validation_requires_pay_type(): void
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('users.employment.update', $this->employee), [
            'employment_status' => EmploymentStatus::Active->value,
            'currency' => 'GBP',
        ]);

        $response->assertSessionHasErrors('pay_type');
    }

    public function test_max_hours_must_be_greater_than_min_hours(): void
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('users.employment.update', $this->employee), [
            'employment_status' => EmploymentStatus::Active->value,
            'pay_type' => PayType::Hourly->value,
            'currency' => 'GBP',
            'min_hours_per_week' => 40,
            'max_hours_per_week' => 20,
        ]);

        $response->assertSessionHasErrors('max_hours_per_week');
    }

    public function test_admin_from_different_tenant_cannot_update_employment_details(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherAdmin = User::factory()->create(['tenant_id' => $otherTenant->id]);
        UserRoleAssignment::create([
            'user_id' => $otherAdmin->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        $this->actingAs($otherAdmin);

        $response = $this->put(route('users.employment.update', $this->employee), [
            'employment_status' => EmploymentStatus::Active->value,
            'pay_type' => PayType::Hourly->value,
            'currency' => 'GBP',
        ]);

        $response->assertStatus(403);
    }

    public function test_user_show_page_displays_employment_details(): void
    {
        UserEmploymentDetails::factory()->create([
            'user_id' => $this->employee->id,
            'employment_status' => EmploymentStatus::Active,
            'employment_start_date' => '2023-01-15',
        ]);

        $this->actingAs($this->admin);

        $response = $this->get(route('users.show', $this->employee));

        $response->assertOk();
        $response->assertSee('Employment Details');
        $response->assertSee('Active');
    }
}
