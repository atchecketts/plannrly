<?php

namespace Database\Factories;

use App\Enums\ShiftStatus;
use App\Models\BusinessRole;
use App\Models\Department;
use App\Models\Location;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Shift>
 */
class ShiftFactory extends Factory
{
    public function definition(): array
    {
        $startHour = fake()->numberBetween(6, 14);
        $duration = fake()->randomElement([4, 6, 8, 10]);

        return [
            'tenant_id' => Tenant::factory(),
            'location_id' => Location::factory(),
            'department_id' => Department::factory(),
            'business_role_id' => BusinessRole::factory(),
            'user_id' => null,
            'date' => fake()->dateTimeBetween('now', '+2 weeks'),
            'start_time' => sprintf('%02d:00', $startHour),
            'end_time' => sprintf('%02d:00', $startHour + $duration),
            'break_duration_minutes' => $duration >= 6 ? 30 : null,
            'notes' => null,
            'status' => ShiftStatus::Draft,
            'is_recurring' => false,
            'recurrence_rule' => null,
            'parent_shift_id' => null,
            'created_by' => null,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
        ]);
    }

    public function assigned(?User $user = null): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user?->id ?? User::factory(),
        ]);
    }

    public function unassigned(): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => null,
        ]);
    }

    public function onDate($date): static
    {
        return $this->state(fn (array $attributes) => [
            'date' => $date,
        ]);
    }

    public function morning(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => '06:00',
            'end_time' => '14:00',
        ]);
    }

    public function afternoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => '14:00',
            'end_time' => '22:00',
        ]);
    }

    public function night(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => '22:00',
            'end_time' => '06:00',
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ShiftStatus::Published,
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ShiftStatus::Completed,
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ShiftStatus::Cancelled,
        ]);
    }

    /**
     * Configure the shift as a recurring parent (template).
     */
    public function recurring(string $frequency = 'weekly', int $interval = 1, ?array $daysOfWeek = null): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
            'recurrence_rule' => [
                'frequency' => $frequency,
                'interval' => $interval,
                'days_of_week' => $daysOfWeek ?? [1], // Default to Monday
                'end_date' => null,
                'end_after_occurrences' => null,
            ],
            'parent_shift_id' => null,
        ]);
    }

    /**
     * Configure the shift as a recurring child with end date.
     */
    public function recurringWithEndDate(string $endDate, string $frequency = 'weekly', int $interval = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
            'recurrence_rule' => [
                'frequency' => $frequency,
                'interval' => $interval,
                'days_of_week' => [1],
                'end_date' => $endDate,
                'end_after_occurrences' => null,
            ],
            'parent_shift_id' => null,
        ]);
    }

    /**
     * Configure the shift as a recurring child with occurrence limit.
     */
    public function recurringWithOccurrences(int $occurrences, string $frequency = 'weekly', int $interval = 1): static
    {
        return $this->state(fn (array $attributes) => [
            'is_recurring' => true,
            'recurrence_rule' => [
                'frequency' => $frequency,
                'interval' => $interval,
                'days_of_week' => [1],
                'end_date' => null,
                'end_after_occurrences' => $occurrences,
            ],
            'parent_shift_id' => null,
        ]);
    }

    /**
     * Configure the shift as a child of a recurring parent.
     */
    public function childOf(\App\Models\Shift $parentShift): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $parentShift->tenant_id,
            'location_id' => $parentShift->location_id,
            'department_id' => $parentShift->department_id,
            'business_role_id' => $parentShift->business_role_id,
            'user_id' => $parentShift->user_id,
            'start_time' => $parentShift->start_time->format('H:i'),
            'end_time' => $parentShift->end_time->format('H:i'),
            'break_duration_minutes' => $parentShift->break_duration_minutes,
            'status' => $parentShift->status,
            'is_recurring' => false,
            'recurrence_rule' => null,
            'parent_shift_id' => $parentShift->id,
            'created_by' => $parentShift->created_by,
        ]);
    }
}
