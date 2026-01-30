<?php

namespace Database\Factories;

use App\Enums\ScheduleHistoryAction;
use App\Models\Shift;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ScheduleHistory>
 */
class ScheduleHistoryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'shift_id' => Shift::factory(),
            'user_id' => User::factory(),
            'action' => ScheduleHistoryAction::Created,
            'old_values' => null,
            'new_values' => null,
        ];
    }

    public function created(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => ScheduleHistoryAction::Created,
            'old_values' => null,
            'new_values' => [
                'date' => now()->format('Y-m-d'),
                'start_time' => '09:00',
                'end_time' => '17:00',
                'status' => 'draft',
            ],
        ]);
    }

    public function updated(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => ScheduleHistoryAction::Updated,
            'old_values' => [
                'start_time' => '09:00',
                'end_time' => '17:00',
            ],
            'new_values' => [
                'start_time' => '10:00',
                'end_time' => '18:00',
            ],
        ]);
    }

    public function deleted(): static
    {
        return $this->state(fn (array $attributes) => [
            'action' => ScheduleHistoryAction::Deleted,
            'old_values' => [
                'date' => now()->format('Y-m-d'),
                'start_time' => '09:00',
                'end_time' => '17:00',
                'status' => 'published',
            ],
            'new_values' => null,
        ]);
    }

    public function forShift(Shift $shift): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $shift->tenant_id,
            'shift_id' => $shift->id,
        ]);
    }

    public function byUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
        ]);
    }
}
