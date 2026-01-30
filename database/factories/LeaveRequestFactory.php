<?php

namespace Database\Factories;

use App\Enums\LeaveRequestStatus;
use App\Models\LeaveType;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeaveRequest>
 */
class LeaveRequestFactory extends Factory
{
    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('now', '+2 months');
        $endDate = (clone $startDate)->modify('+'.fake()->numberBetween(1, 5).' days');
        $totalDays = $startDate->diff($endDate)->days + 1;

        return [
            'tenant_id' => Tenant::factory(),
            'user_id' => User::factory(),
            'leave_type_id' => LeaveType::factory(),
            'start_date' => $startDate,
            'end_date' => $endDate,
            'start_half_day' => false,
            'end_half_day' => false,
            'total_days' => $totalDays,
            'reason' => fake()->sentence(),
            'status' => LeaveRequestStatus::Draft,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'review_notes' => null,
        ];
    }

    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $user->tenant_id,
            'user_id' => $user->id,
        ]);
    }

    public function draft(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeaveRequestStatus::Draft,
        ]);
    }

    public function requested(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeaveRequestStatus::Requested,
        ]);
    }

    public function approved(?User $reviewer = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeaveRequestStatus::Approved,
            'reviewed_by' => $reviewer?->id,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(?User $reviewer = null, ?string $notes = null): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => LeaveRequestStatus::Rejected,
            'reviewed_by' => $reviewer?->id,
            'reviewed_at' => now(),
            'review_notes' => $notes ?? 'Request denied.',
        ]);
    }

    public function forDateRange($startDate, $endDate): static
    {
        $totalDays = $startDate->diff($endDate)->days + 1;

        return $this->state(fn (array $attributes) => [
            'start_date' => $startDate,
            'end_date' => $endDate,
            'total_days' => $totalDays,
        ]);
    }

    public function startHalfDay(): static
    {
        return $this->state(fn (array $attributes) => [
            'start_half_day' => true,
            'total_days' => max(0.5, ($attributes['total_days'] ?? 1) - 0.5),
        ]);
    }

    public function endHalfDay(): static
    {
        return $this->state(fn (array $attributes) => [
            'end_half_day' => true,
            'total_days' => max(0.5, ($attributes['total_days'] ?? 1) - 0.5),
        ]);
    }
}
