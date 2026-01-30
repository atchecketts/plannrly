<?php

namespace App\Services;

use App\Models\Shift;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class RecurringShiftService
{
    /**
     * Default number of weeks to generate shifts for.
     */
    protected int $generationWeeks = 12;

    /**
     * Generate child shift instances from a recurring parent shift.
     *
     * @return Collection<int, Shift>
     */
    public function generateInstances(Shift $parentShift): Collection
    {
        if (! $parentShift->is_recurring || empty($parentShift->recurrence_rule)) {
            return collect();
        }

        $occurrences = $this->calculateOccurrenceDates($parentShift);
        $createdShifts = collect();

        foreach ($occurrences as $date) {
            // Skip the parent shift's date - it's already created
            if ($date->isSameDay($parentShift->date)) {
                continue;
            }

            $childShift = $parentShift->replicate(['id', 'created_at', 'updated_at']);
            $childShift->date = $date;
            $childShift->is_recurring = false;
            $childShift->recurrence_rule = null;
            $childShift->parent_shift_id = $parentShift->id;
            $childShift->save();

            $createdShifts->push($childShift);
        }

        return $createdShifts;
    }

    /**
     * Calculate all occurrence dates based on recurrence rule.
     *
     * @return Collection<int, Carbon>
     */
    public function calculateOccurrenceDates(Shift $parentShift): Collection
    {
        $rule = $parentShift->recurrence_rule;

        if (empty($rule) || empty($rule['frequency'])) {
            return collect();
        }

        $startDate = Carbon::parse($parentShift->date);
        $endDate = $this->calculateEndDate($startDate, $rule);
        $interval = $rule['interval'] ?? 1;
        $frequency = $rule['frequency'];
        $maxOccurrences = $rule['end_after_occurrences'] ?? null;

        $occurrences = collect();
        $count = 0;

        // Different handling based on frequency
        if ($frequency === 'weekly' && ! empty($rule['days_of_week'])) {
            // Weekly with specific days of week (0=Sun, 1=Mon, ..., 6=Sat)
            $daysOfWeek = $rule['days_of_week'];
            sort($daysOfWeek);

            // Start from the Sunday of the week containing the start date
            $currentWeekSunday = $startDate->copy()->startOfWeek(Carbon::SUNDAY);
            $weekCount = 0;

            while ($currentWeekSunday->lte($endDate) && ($maxOccurrences === null || $count < $maxOccurrences)) {
                // Only process weeks that match the interval
                if ($weekCount % $interval === 0) {
                    foreach ($daysOfWeek as $dayOfWeek) {
                        // dayOfWeek: 0=Sunday, 1=Monday, etc.
                        $date = $currentWeekSunday->copy()->addDays($dayOfWeek);

                        // Must be on or after start date and before/on end date
                        if ($date->gte($startDate) && $date->lte($endDate)) {
                            if ($maxOccurrences === null || $count < $maxOccurrences) {
                                $occurrences->push($date->copy());
                                $count++;
                            }
                        }
                    }
                }

                $currentWeekSunday->addWeek();
                $weekCount++;
            }
        } elseif ($frequency === 'daily') {
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate) && ($maxOccurrences === null || $count < $maxOccurrences)) {
                $occurrences->push($currentDate->copy());
                $count++;
                $currentDate->addDays($interval);
            }
        } elseif ($frequency === 'monthly') {
            $currentDate = $startDate->copy();
            $originalDay = $startDate->day;
            while ($currentDate->lte($endDate) && ($maxOccurrences === null || $count < $maxOccurrences)) {
                // Try to set the same day, skip if doesn't exist
                if ($originalDay <= $currentDate->daysInMonth) {
                    $currentDate->day($originalDay);
                    $occurrences->push($currentDate->copy());
                    $count++;
                }
                $currentDate->addMonths($interval)->startOfMonth();
            }
        } else {
            // Weekly without specific days - just same day each week
            $currentDate = $startDate->copy();
            while ($currentDate->lte($endDate) && ($maxOccurrences === null || $count < $maxOccurrences)) {
                $occurrences->push($currentDate->copy());
                $count++;
                $currentDate->addWeeks($interval);
            }
        }

        return $occurrences->unique(fn (Carbon $date) => $date->format('Y-m-d'))->values();
    }

    /**
     * Update all future child instances of a recurring shift.
     */
    public function updateFutureInstances(Shift $parentShift, array $data): int
    {
        $today = Carbon::today();

        return $parentShift->childShifts()
            ->where('date', '>=', $today)
            ->update($data);
    }

    /**
     * Delete all future child instances of a recurring shift.
     */
    public function deleteFutureInstances(Shift $parentShift): int
    {
        $today = Carbon::today();

        return $parentShift->childShifts()
            ->where('date', '>=', $today)
            ->delete();
    }

    /**
     * Detach a child shift from its parent (for individual editing).
     */
    public function detachFromParent(Shift $childShift): Shift
    {
        $childShift->update([
            'parent_shift_id' => null,
        ]);

        return $childShift->fresh();
    }

    /**
     * Extend recurring shifts that are approaching their generation window end.
     */
    public function extendRecurringShifts(): int
    {
        $extendedCount = 0;
        $extensionThreshold = Carbon::today()->addWeeks(4);

        // Find recurring parent shifts that need extension
        // Use withoutGlobalScopes since this runs via artisan command without auth
        $parentShifts = Shift::withoutGlobalScopes()
            ->whereNotNull('recurrence_rule')
            ->where('is_recurring', true)
            ->whereNull('parent_shift_id')
            ->get();

        foreach ($parentShifts as $parentShift) {
            $rule = $parentShift->recurrence_rule;

            // Skip if recurrence has a hard end date or occurrence limit
            if (! empty($rule['end_date']) || ! empty($rule['end_after_occurrences'])) {
                continue;
            }

            // Find the latest generated child shift
            $latestChild = $parentShift->childShifts()
                ->orderBy('date', 'desc')
                ->first();

            $latestDate = $latestChild ? Carbon::parse($latestChild->date) : Carbon::parse($parentShift->date);

            // If latest shift is within threshold, extend
            if ($latestDate->lt($extensionThreshold)) {
                $newShifts = $this->generateInstancesFromDate($parentShift, $latestDate->addDay());
                $extendedCount += $newShifts->count();
            }
        }

        return $extendedCount;
    }

    /**
     * Generate instances starting from a specific date.
     *
     * @return Collection<int, Shift>
     */
    protected function generateInstancesFromDate(Shift $parentShift, Carbon $fromDate): Collection
    {
        $rule = $parentShift->recurrence_rule;
        $endDate = $fromDate->copy()->addWeeks($this->generationWeeks);
        $interval = $rule['interval'] ?? 1;
        $frequency = $rule['frequency'];

        $createdShifts = collect();

        // Calculate potential dates using similar logic to calculateOccurrenceDates
        $potentialDates = collect();

        if ($frequency === 'weekly' && ! empty($rule['days_of_week'])) {
            $daysOfWeek = $rule['days_of_week'];
            sort($daysOfWeek);
            $currentWeekSunday = $fromDate->copy()->startOfWeek(Carbon::SUNDAY);
            $weekCount = 0;

            while ($currentWeekSunday->lte($endDate)) {
                if ($weekCount % $interval === 0) {
                    foreach ($daysOfWeek as $dayOfWeek) {
                        $date = $currentWeekSunday->copy()->addDays($dayOfWeek);
                        if ($date->gte($fromDate) && $date->lte($endDate)) {
                            $potentialDates->push($date->copy());
                        }
                    }
                }
                $currentWeekSunday->addWeek();
                $weekCount++;
            }
        } elseif ($frequency === 'daily') {
            $currentDate = $fromDate->copy();
            while ($currentDate->lte($endDate)) {
                $potentialDates->push($currentDate->copy());
                $currentDate->addDays($interval);
            }
        } elseif ($frequency === 'monthly') {
            $currentDate = $fromDate->copy();
            $originalDay = Carbon::parse($parentShift->date)->day;
            while ($currentDate->lte($endDate)) {
                if ($originalDay <= $currentDate->daysInMonth) {
                    $currentDate->day($originalDay);
                    if ($currentDate->gte($fromDate)) {
                        $potentialDates->push($currentDate->copy());
                    }
                }
                $currentDate->addMonths($interval)->startOfMonth();
            }
        } else {
            $currentDate = $fromDate->copy();
            while ($currentDate->lte($endDate)) {
                $potentialDates->push($currentDate->copy());
                $currentDate->addWeeks($interval);
            }
        }

        // Create shifts for dates that don't already exist
        foreach ($potentialDates as $date) {
            $exists = Shift::withoutGlobalScopes()
                ->where('parent_shift_id', $parentShift->id)
                ->whereDate('date', $date)
                ->exists();

            if (! $exists) {
                $childShift = $parentShift->replicate(['id', 'created_at', 'updated_at']);
                $childShift->date = $date;
                $childShift->is_recurring = false;
                $childShift->recurrence_rule = null;
                $childShift->parent_shift_id = $parentShift->id;
                $childShift->save();

                $createdShifts->push($childShift);
            }
        }

        return $createdShifts;
    }

    /**
     * Calculate end date based on recurrence rule or default generation window.
     */
    protected function calculateEndDate(Carbon $startDate, array $rule): Carbon
    {
        if (! empty($rule['end_date'])) {
            $ruleEndDate = Carbon::parse($rule['end_date']);
            $defaultEndDate = $startDate->copy()->addWeeks($this->generationWeeks);

            // Use the earlier of the two dates
            return $ruleEndDate->lt($defaultEndDate) ? $ruleEndDate : $defaultEndDate;
        }

        return $startDate->copy()->addWeeks($this->generationWeeks);
    }

    /**
     * Get next occurrence dates based on frequency.
     *
     * @return array<int, Carbon>
     */
    protected function getNextOccurrences(Carbon $currentDate, string $frequency, int $interval, array $rule): array
    {
        $dates = [];

        switch ($frequency) {
            case 'daily':
                $dates[] = $currentDate->copy()->addDays($interval);
                break;

            case 'weekly':
                $daysOfWeek = $rule['days_of_week'] ?? [$currentDate->dayOfWeek];

                // Find next occurrences within this week and next
                $weekStart = $currentDate->copy()->startOfWeek();

                for ($week = 0; $week <= $interval; $week++) {
                    foreach ($daysOfWeek as $day) {
                        $date = $weekStart->copy()->addWeeks($week * $interval)->addDays($day);

                        if ($date->gt($currentDate)) {
                            $dates[] = $date;
                        }
                    }
                }

                // Sort and take only unique dates
                usort($dates, fn ($a, $b) => $a->timestamp <=> $b->timestamp);
                break;

            case 'monthly':
                $nextDate = $currentDate->copy()->addMonths($interval);
                // Try to keep same day of month
                $targetDay = Carbon::parse($rule['original_day'] ?? $currentDate->day);

                if ($targetDay->day <= $nextDate->daysInMonth) {
                    $nextDate->day($targetDay->day);
                } else {
                    // Skip this month if day doesn't exist
                    $nextDate = null;
                }

                if ($nextDate) {
                    $dates[] = $nextDate;
                }
                break;
        }

        return $dates;
    }

    /**
     * Validate that a date is valid for the recurrence rule.
     */
    protected function isValidDate(Carbon $date, array $rule): bool
    {
        $frequency = $rule['frequency'] ?? 'daily';

        if ($frequency === 'weekly' && ! empty($rule['days_of_week'])) {
            return in_array($date->dayOfWeek, $rule['days_of_week']);
        }

        return true;
    }

    /**
     * Set the generation window in weeks.
     */
    public function setGenerationWeeks(int $weeks): self
    {
        $this->generationWeeks = $weeks;

        return $this;
    }
}
