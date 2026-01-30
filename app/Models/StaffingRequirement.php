<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StaffingRequirement extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'location_id',
        'department_id',
        'business_role_id',
        'day_of_week',
        'start_time',
        'end_time',
        'min_employees',
        'max_employees',
        'is_active',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'day_of_week' => 'integer',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'min_employees' => 'integer',
            'max_employees' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function businessRole(): BelongsTo
    {
        return $this->belongsTo(BusinessRole::class);
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function scopeForDayOfWeek(Builder $query, int $dayOfWeek): Builder
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    /**
     * Filter requirements that overlap with a given time slot.
     */
    public function scopeForTimeSlot(Builder $query, string $startTime, string $endTime): Builder
    {
        return $query->where(function ($q) use ($startTime, $endTime) {
            // Requirement overlaps with the given time slot
            $q->where('start_time', '<', $endTime)
                ->where('end_time', '>', $startTime);
        });
    }

    public function scopeForLocation(Builder $query, ?int $locationId): Builder
    {
        if ($locationId === null) {
            return $query->whereNull('location_id');
        }

        return $query->where(function ($q) use ($locationId) {
            $q->where('location_id', $locationId)
                ->orWhereNull('location_id');
        });
    }

    public function scopeForDepartment(Builder $query, ?int $departmentId): Builder
    {
        if ($departmentId === null) {
            return $query->whereNull('department_id');
        }

        return $query->where(function ($q) use ($departmentId) {
            $q->where('department_id', $departmentId)
                ->orWhereNull('department_id');
        });
    }

    public function scopeForBusinessRole(Builder $query, int $businessRoleId): Builder
    {
        return $query->where('business_role_id', $businessRoleId);
    }

    /**
     * Get the day name for the requirement.
     */
    public function getDayNameAttribute(): string
    {
        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return $days[$this->day_of_week] ?? 'Unknown';
    }

    /**
     * Get the short day name for the requirement.
     */
    public function getShortDayNameAttribute(): string
    {
        $days = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];

        return $days[$this->day_of_week] ?? '???';
    }

    /**
     * Get the time window formatted as a string.
     */
    public function getTimeWindowAttribute(): string
    {
        $start = $this->start_time?->format('H:i') ?? '00:00';
        $end = $this->end_time?->format('H:i') ?? '00:00';

        return "{$start} - {$end}";
    }

    /**
     * Get the staffing range formatted as a string.
     */
    public function getStaffingRangeAttribute(): string
    {
        if ($this->max_employees === null) {
            return "{$this->min_employees}+";
        }

        if ($this->min_employees === $this->max_employees) {
            return (string) $this->min_employees;
        }

        return "{$this->min_employees} - {$this->max_employees}";
    }

    /**
     * Get the scope description (location/department or "All").
     */
    public function getScopeDescriptionAttribute(): string
    {
        $parts = [];

        if ($this->location) {
            $parts[] = $this->location->name;
        }

        if ($this->department) {
            $parts[] = $this->department->name;
        }

        return empty($parts) ? 'All Locations/Departments' : implode(' / ', $parts);
    }

    /**
     * Check if a given employee count meets this requirement.
     */
    public function isMet(int $employeeCount): bool
    {
        if ($employeeCount < $this->min_employees) {
            return false;
        }

        if ($this->max_employees !== null && $employeeCount > $this->max_employees) {
            return false;
        }

        return true;
    }

    /**
     * Check if understaffed based on employee count.
     */
    public function isUnderstaffed(int $employeeCount): bool
    {
        return $employeeCount < $this->min_employees;
    }

    /**
     * Check if overstaffed based on employee count.
     */
    public function isOverstaffed(int $employeeCount): bool
    {
        return $this->max_employees !== null && $employeeCount > $this->max_employees;
    }
}
