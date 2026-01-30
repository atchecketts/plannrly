<?php

namespace Tests\Feature;

use App\Enums\SystemRole;
use App\Models\Tenant;
use App\Models\TenantSettings;
use App\Models\User;
use App\Models\UserRoleAssignment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TenantSettingsManagementTest extends TestCase
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

    public function test_admin_can_view_settings_page(): void
    {
        $this->actingAs($this->admin);

        $response = $this->get(route('settings.edit'));

        $response->assertOk();
        $response->assertViewIs('settings.edit');
        $response->assertViewHas('settings');
        $response->assertViewHas('timezones');
        $response->assertViewHas('currencies');
        $response->assertViewHas('carryoverModes');
    }

    public function test_employee_cannot_view_settings_page(): void
    {
        $this->actingAs($this->employee);

        $response = $this->get(route('settings.edit'));

        $response->assertStatus(403);
    }

    public function test_settings_are_created_with_tenant(): void
    {
        // TenantObserver automatically creates TenantSettings when a Tenant is created
        $this->assertDatabaseHas('tenant_settings', ['tenant_id' => $this->tenant->id]);
    }

    public function test_admin_can_update_scheduling_settings(): void
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('settings.update'), [
            'day_starts_at' => '08:00',
            'day_ends_at' => '20:00',
            'week_starts_on' => 0,
            'timezone' => 'America/New_York',
            'date_format' => 'm/d/Y',
            'time_format' => 'h:i A',
            'missed_grace_minutes' => 30,
            'leave_carryover_mode' => 'partial',
            'default_currency' => 'USD',
            'primary_color' => '#3B82F6',
        ]);

        $response->assertRedirect(route('settings.edit'));
        $response->assertSessionHas('success');

        $settings = TenantSettings::where('tenant_id', $this->tenant->id)->first();
        $this->assertEquals('08:00:00', $settings->day_starts_at->format('H:i:s'));
        $this->assertEquals('20:00:00', $settings->day_ends_at->format('H:i:s'));
        $this->assertEquals(0, $settings->week_starts_on);
        $this->assertEquals('America/New_York', $settings->timezone);
        $this->assertEquals('m/d/Y', $settings->date_format);
        $this->assertEquals('h:i A', $settings->time_format);
        $this->assertEquals(30, $settings->missed_grace_minutes);
        $this->assertEquals('partial', $settings->leave_carryover_mode);
        $this->assertEquals('USD', $settings->default_currency);
        $this->assertEquals('#3B82F6', $settings->primary_color);
    }

    public function test_admin_can_update_feature_toggles(): void
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('settings.update'), [
            'enable_clock_in_out' => true,
            'enable_shift_acknowledgement' => true,
            'notify_on_publish' => true,
            'day_starts_at' => '06:00',
            'day_ends_at' => '22:00',
            'week_starts_on' => 1,
            'timezone' => 'Europe/London',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'missed_grace_minutes' => 15,
            'leave_carryover_mode' => 'none',
            'default_currency' => 'GBP',
            'primary_color' => '#6366f1',
        ]);

        $response->assertRedirect(route('settings.edit'));

        $settings = TenantSettings::where('tenant_id', $this->tenant->id)->first();
        $this->assertTrue($settings->enable_clock_in_out);
        $this->assertTrue($settings->enable_shift_acknowledgement);
        $this->assertTrue($settings->notify_on_publish);
    }

    public function test_employee_cannot_update_settings(): void
    {
        $this->actingAs($this->employee);

        $response = $this->put(route('settings.update'), [
            'day_starts_at' => '08:00',
            'day_ends_at' => '20:00',
            'week_starts_on' => 1,
            'timezone' => 'Europe/London',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'missed_grace_minutes' => 15,
            'leave_carryover_mode' => 'none',
            'default_currency' => 'GBP',
            'primary_color' => '#6366f1',
        ]);

        $response->assertStatus(403);
    }

    public function test_settings_require_valid_timezone(): void
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('settings.update'), [
            'day_starts_at' => '06:00',
            'day_ends_at' => '22:00',
            'week_starts_on' => 1,
            'timezone' => 'Invalid/Timezone',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'missed_grace_minutes' => 15,
            'leave_carryover_mode' => 'none',
            'default_currency' => 'GBP',
            'primary_color' => '#6366f1',
        ]);

        $response->assertSessionHasErrors('timezone');
    }

    public function test_settings_require_valid_carryover_mode(): void
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('settings.update'), [
            'day_starts_at' => '06:00',
            'day_ends_at' => '22:00',
            'week_starts_on' => 1,
            'timezone' => 'Europe/London',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'missed_grace_minutes' => 15,
            'leave_carryover_mode' => 'invalid',
            'default_currency' => 'GBP',
            'primary_color' => '#6366f1',
        ]);

        $response->assertSessionHasErrors('leave_carryover_mode');
    }

    public function test_settings_require_valid_color(): void
    {
        $this->actingAs($this->admin);

        $response = $this->put(route('settings.update'), [
            'day_starts_at' => '06:00',
            'day_ends_at' => '22:00',
            'week_starts_on' => 1,
            'timezone' => 'Europe/London',
            'date_format' => 'd/m/Y',
            'time_format' => 'H:i',
            'missed_grace_minutes' => 15,
            'leave_carryover_mode' => 'none',
            'default_currency' => 'GBP',
            'primary_color' => 'not-a-color',
        ]);

        $response->assertSessionHasErrors('primary_color');
    }

    public function test_different_tenants_have_separate_settings(): void
    {
        $otherTenant = Tenant::factory()->create();
        $otherAdmin = User::factory()->create(['tenant_id' => $otherTenant->id]);
        UserRoleAssignment::create([
            'user_id' => $otherAdmin->id,
            'system_role' => SystemRole::Admin->value,
        ]);

        // Both tenants have settings created by observer
        $this->assertDatabaseCount('tenant_settings', 2);

        $tenant1Settings = TenantSettings::where('tenant_id', $this->tenant->id)->first();
        $tenant2Settings = TenantSettings::where('tenant_id', $otherTenant->id)->first();

        $this->assertNotEquals($tenant1Settings->id, $tenant2Settings->id);
    }
}
