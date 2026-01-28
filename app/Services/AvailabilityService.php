<?php

namespace App\Services;

use App\Enums\AvailabilityType;
use App\Enums\PreferenceLevel;
use App\Models\User;
use App\Models\UserAvailability;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    /**
     * Get the availability status for a user on a specific date and time.
     *
     * @return array{is_available: bool, preference_level: PreferenceLevel, rule: ?UserAvailability}
     */
    public function getAvailabilityAt(User $user, Carbon $date, ?string $startTime = null, ?string $endTime = null): array
    {
        $specificRule = $this->findSpecificDateRule($user, $date, $startTime);

        if ($specificRule) {
            return [
                'is_available' => $specificRule->is_available,
                'preference_level' => $specificRule->preference_level,
                'rule' => $specificRule,
            ];
        }

        $recurringRule = $this->findRecurringRule($user, $date, $startTime);

        if ($recurringRule) {
            return [
                'is_available' => $recurringRule->is_available,
                'preference_level' => $recurringRule->preference_level,
                'rule' => $recurringRule,
            ];
        }

        return [
            'is_available' => true,
            'preference_level' => PreferenceLevel::Available,
            'rule' => null,
        ];
    }

    /**
     * Check if a user is available for a shift.
     */
    public function isAvailableForShift(User $user, Carbon $date, string $startTime, string $endTime): bool
    {
        $availability = $this->getAvailabilityAt($user, $date, $startTime);

        return $availability['is_available'] && $availability['preference_level']->canSchedule();
    }

    /**
     * Get the preference level for a user on a specific date and time.
     */
    public function getPreferenceLevel(User $user, Carbon $date, ?string $startTime = null): PreferenceLevel
    {
        $availability = $this->getAvailabilityAt($user, $date, $startTime);

        return $availability['preference_level'];
    }

    /**
     * Check if scheduling would conflict with availability.
     */
    public function hasConflict(User $user, Carbon $date, string $startTime, string $endTime): bool
    {
        $availability = $this->getAvailabilityAt($user, $date, $startTime);

        return ! $availability['is_available'] || $availability['preference_level']->isUnavailable();
    }

    /**
     * Get availability warnings for a shift assignment.
     *
     * @return array<string>
     */
    public function getWarnings(User $user, Carbon $date, string $startTime, string $endTime): array
    {
        $warnings = [];
        $availability = $this->getAvailabilityAt($user, $date, $startTime);

        if (! $availability['is_available']) {
            $warnings[] = "{$user->full_name} is marked as unavailable on this date";
        } elseif ($availability['preference_level'] === PreferenceLevel::Unavailable) {
            $warnings[] = "{$user->full_name} has marked themselves as unavailable at this time";
        } elseif ($availability['preference_level'] === PreferenceLevel::IfNeeded) {
            $warnings[] = "{$user->full_name} prefers not to work at this time (if needed only)";
        }

        return $warnings;
    }

    /**
     * Get all availability rules for a user on a specific week.
     *
     * @return Collection<int, UserAvailability>
     */
    public function getWeeklyAvailability(User $user, Carbon $weekStart): Collection
    {
        $weekEnd = $weekStart->copy()->endOfWeek();

        return UserAvailability::where('user_id', $user->id)
            ->effective($weekStart)
            ->where(function ($query) use ($weekStart, $weekEnd) {
                $query->where('type', AvailabilityType::Recurring)
                    ->orWhere(function ($q) use ($weekStart, $weekEnd) {
                        $q->where('type', AvailabilityType::SpecificDate)
                            ->whereBetween('specific_date', [$weekStart, $weekEnd]);
                    });
            })
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get();
    }

    /**
     * Get all users available for a specific date and time range.
     *
     * @return Collection<int, User>
     */
    public function getAvailableUsers(Collection $users, Carbon $date, string $startTime, string $endTime): Collection
    {
        return $users->filter(function (User $user) use ($date, $startTime, $endTime) {
            return $this->isAvailableForShift($user, $date, $startTime, $endTime);
        });
    }

    /**
     * Sort users by availability preference (preferred first).
     *
     * @return Collection<int, User>
     */
    public function sortByPreference(Collection $users, Carbon $date, ?string $startTime = null): Collection
    {
        return $users->sortBy(function (User $user) use ($date, $startTime) {
            $preference = $this->getPreferenceLevel($user, $date, $startTime);

            return $preference->priority();
        });
    }

    /**
     * Find a specific date rule for a user.
     */
    protected function findSpecificDateRule(User $user, Carbon $date, ?string $time = null): ?UserAvailability
    {
        $query = UserAvailability::where('user_id', $user->id)
            ->where('type', AvailabilityType::SpecificDate)
            ->where('specific_date', $date->toDateString())
            ->effective($date);

        $rules = $query->get();

        if ($time && $rules->isNotEmpty()) {
            $matchingRule = $rules->first(fn ($rule) => $rule->coversTime($time));
            if ($matchingRule) {
                return $matchingRule;
            }
        }

        return $rules->first();
    }

    /**
     * Find a recurring rule for a user on a given day.
     */
    protected function findRecurringRule(User $user, Carbon $date, ?string $time = null): ?UserAvailability
    {
        $query = UserAvailability::where('user_id', $user->id)
            ->where('type', AvailabilityType::Recurring)
            ->where('day_of_week', $date->dayOfWeek)
            ->effective($date);

        $rules = $query->get();

        if ($time && $rules->isNotEmpty()) {
            $matchingRule = $rules->first(fn ($rule) => $rule->coversTime($time));
            if ($matchingRule) {
                return $matchingRule;
            }
        }

        return $rules->first();
    }

    /**
     * Get a summary of a user's weekly availability.
     *
     * @return array<int, array{day: string, slots: array}>
     */
    public function getWeeklySummary(User $user): array
    {
        $summary = [];
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        $recurringRules = UserAvailability::where('user_id', $user->id)
            ->where('type', AvailabilityType::Recurring)
            ->effective()
            ->orderBy('day_of_week')
            ->orderBy('start_time')
            ->get()
            ->groupBy('day_of_week');

        foreach ($days as $index => $day) {
            $dayRules = $recurringRules->get($index, collect());
            $summary[$index] = [
                'day' => $day,
                'slots' => $dayRules->map(fn ($rule) => [
                    'time_range' => $rule->time_range,
                    'preference_level' => $rule->preference_level,
                    'is_available' => $rule->is_available,
                ])->toArray(),
            ];
        }

        return $summary;
    }
}
