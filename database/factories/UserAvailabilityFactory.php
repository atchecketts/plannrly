<?php

namespace Database\Factories;

use App\Enums\AvailabilityType;
use App\Enums\PreferenceLevel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\UserAvailability>
 */
class UserAvailabilityFactory extends Factory
{
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'type' => AvailabilityType::Recurring,
            'day_of_week' => fake()->numberBetween(0, 6),
            'specific_date' => null,
            'start_time' => '09:00',
            'end_time' => '17:00',
            'is_available' => true,
            'preference_level' => PreferenceLevel::Available,
            'notes' => null,
            'effective_from' => null,
            'effective_until' => null,
        ];
    }

    public function recurring(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AvailabilityType::Recurring,
            'specific_date' => null,
            'day_of_week' => fake()->numberBetween(0, 6),
        ]);
    }

    public function specificDate(?Carbon $date = null): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AvailabilityType::SpecificDate,
            'specific_date' => $date ?? fake()->dateTimeBetween('now', '+1 month'),
            'day_of_week' => null,
        ]);
    }

    public function forDayOfWeek(int $dayOfWeek): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => AvailabilityType::Recurring,
            'day_of_week' => $dayOfWeek,
            'specific_date' => null,
        ]);
    }

    public function preferred(): static
    {
        return $this->state(fn (array $attributes) => [
            'preference_level' => PreferenceLevel::Preferred,
            'is_available' => true,
        ]);
    }

    public function available(): static
    {
        return $this->state(fn (array $attributes) => [
            'preference_level' => PreferenceLevel::Available,
            'is_available' => true,
        ]);
    }

    public function ifNeeded(): static
    {
        return $this->state(fn (array $attributes) => [
            'preference_level' => PreferenceLevel::IfNeeded,
            'is_available' => true,
        ]);
    }

    public function unavailable(): static
    {
        return $this->state(fn (array $attributes) => [
            'preference_level' => PreferenceLevel::Unavailable,
            'is_available' => false,
        ]);
    }

    public function allDay(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => null,
            'end_time' => null,
        ]);
    }

    public function morning(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => '06:00',
            'end_time' => '12:00',
        ]);
    }

    public function afternoon(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => '12:00',
            'end_time' => '18:00',
        ]);
    }

    public function evening(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_time' => '18:00',
            'end_time' => '23:00',
        ]);
    }

    public function effectiveFrom(Carbon $date): static
    {
        return $this->state(fn (array $attributes) => [
            'effective_from' => $date,
        ]);
    }

    public function effectiveUntil(Carbon $date): static
    {
        return $this->state(fn (array $attributes) => [
            'effective_until' => $date,
        ]);
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
