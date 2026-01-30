<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TenantSettings>
 */
class TenantSettingsFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'enable_clock_in_out' => false,
            'enable_shift_acknowledgement' => false,
            'day_starts_at' => '00:00:00',
            'day_ends_at' => '23:59:59',
            'week_starts_on' => 1,
            'timezone' => fake()->timezone(),
            'date_format' => 'Y-m-d',
            'time_format' => 'H:i',
            'missed_grace_minutes' => 15,
            'notify_on_publish' => true,
            'require_admin_approval_for_swaps' => true,
            'leave_carryover_mode' => 'none',
            'default_currency' => 'GBP',
            'primary_color' => '#6366f1',
            'clock_in_grace_minutes' => 15,
            'require_gps_clock_in' => false,
            'auto_clock_out_enabled' => false,
            'auto_clock_out_time' => null,
            'overtime_threshold_minutes' => 480,
            'require_manager_approval' => false,
            'enable_shift_reminders' => true,
            'remind_day_before' => true,
            'remind_hours_before' => true,
            'remind_hours_before_value' => 1,
        ];
    }

    public function withClockInEnabled(): static
    {
        return $this->state(fn (array $attributes) => [
            'enable_clock_in_out' => true,
        ]);
    }

    public function withGpsRequired(): static
    {
        return $this->state(fn (array $attributes) => [
            'enable_clock_in_out' => true,
            'require_gps_clock_in' => true,
        ]);
    }

    public function withManagerApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'enable_clock_in_out' => true,
            'require_manager_approval' => true,
        ]);
    }

    public function withAutoClockOut(string $time = '23:00:00'): static
    {
        return $this->state(fn (array $attributes) => [
            'enable_clock_in_out' => true,
            'auto_clock_out_enabled' => true,
            'auto_clock_out_time' => $time,
        ]);
    }
}
