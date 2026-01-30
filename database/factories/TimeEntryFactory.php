<?php

namespace Database\Factories;

use App\Enums\TimeEntryStatus;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TimeEntry>
 */
class TimeEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'shift_id' => Shift::factory(),
            'clock_in_at' => null,
            'clock_out_at' => null,
            'break_start_at' => null,
            'break_end_at' => null,
            'actual_break_minutes' => null,
            'notes' => null,
            'clock_in_location' => null,
            'clock_out_location' => null,
            'status' => TimeEntryStatus::ClockedIn,
            'approved_by' => null,
            'approved_at' => null,
            'adjustment_reason' => null,
        ];
    }

    public function forShift(Shift $shift): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $shift->tenant_id,
            'user_id' => $shift->user_id,
            'shift_id' => $shift->id,
        ]);
    }

    public function clockedIn(): static
    {
        return $this->state(fn (array $attributes) => [
            'clock_in_at' => now(),
            'status' => TimeEntryStatus::ClockedIn,
        ]);
    }

    public function onBreak(): static
    {
        return $this->state(fn (array $attributes) => [
            'clock_in_at' => now()->subHours(2),
            'break_start_at' => now(),
            'status' => TimeEntryStatus::OnBreak,
        ]);
    }

    public function clockedOut(): static
    {
        return $this->state(fn (array $attributes) => [
            'clock_in_at' => now()->subHours(8),
            'clock_out_at' => now(),
            'actual_break_minutes' => 30,
            'status' => TimeEntryStatus::ClockedOut,
        ]);
    }

    public function approved(?User $approver = null): static
    {
        return $this->state(fn (array $attributes) => [
            'clock_in_at' => now()->subHours(8),
            'clock_out_at' => now(),
            'actual_break_minutes' => 30,
            'status' => TimeEntryStatus::ClockedOut,
            'approved_by' => $approver?->id ?? User::factory(),
            'approved_at' => now(),
        ]);
    }

    public function pendingApproval(): static
    {
        return $this->state(fn (array $attributes) => [
            'clock_in_at' => now()->subHours(8),
            'clock_out_at' => now(),
            'actual_break_minutes' => 30,
            'status' => TimeEntryStatus::ClockedOut,
            'approved_by' => null,
            'approved_at' => null,
        ]);
    }

    public function adjusted(string $reason = 'Manager adjustment for time correction.'): static
    {
        return $this->state(fn (array $attributes) => [
            'adjustment_reason' => $reason,
        ]);
    }

    public function withLocation(?float $lat = null, ?float $lng = null): static
    {
        $location = [
            'lat' => $lat ?? fake()->latitude(),
            'lng' => $lng ?? fake()->longitude(),
        ];

        return $this->state(fn (array $attributes) => [
            'clock_in_location' => $location,
        ]);
    }
}
