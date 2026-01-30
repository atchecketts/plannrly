<?php

namespace App\Models;

use App\Enums\ShiftStatus;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Shift extends Model
{
    use BelongsToTenant;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'location_id',
        'department_id',
        'business_role_id',
        'user_id',
        'date',
        'start_time',
        'end_time',
        'break_duration_minutes',
        'notes',
        'status',
        'is_recurring',
        'recurrence_rule',
        'parent_shift_id',
        'created_by',
        'reminder_sent_at',
        'hour_reminder_sent_at',
    ];

    protected function casts(): array
    {
        return [
            'date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'status' => ShiftStatus::class,
            'is_recurring' => 'boolean',
            'recurrence_rule' => 'array',
            'reminder_sent_at' => 'datetime',
            'hour_reminder_sent_at' => 'datetime',
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

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function parentShift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'parent_shift_id');
    }

    public function childShifts(): HasMany
    {
        return $this->hasMany(Shift::class, 'parent_shift_id');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function timeEntry(): HasOne
    {
        return $this->hasOne(TimeEntry::class);
    }

    public function swapRequestsAsRequester(): HasMany
    {
        return $this->hasMany(ShiftSwapRequest::class, 'requesting_shift_id');
    }

    public function swapRequestsAsTarget(): HasMany
    {
        return $this->hasMany(ShiftSwapRequest::class, 'target_shift_id');
    }

    public function history(): HasMany
    {
        return $this->hasMany(ScheduleHistory::class);
    }

    public function isAssigned(): bool
    {
        return $this->user_id !== null;
    }

    public function isUnassigned(): bool
    {
        return $this->user_id === null;
    }

    public function getDurationMinutesAttribute(): int
    {
        $start = $this->start_time;
        $end = $this->end_time;

        if ($start && $end) {
            // Handle overnight shifts (end time is before start time)
            if ($end->lt($start)) {
                // Add 24 hours to end time for overnight calculation
                return $start->diffInMinutes($end->copy()->addDay());
            }

            return $start->diffInMinutes($end);
        }

        return 0;
    }

    public function isOvernightShift(): bool
    {
        return $this->start_time && $this->end_time && $this->end_time->lt($this->start_time);
    }

    public function getDurationHoursAttribute(): float
    {
        return round($this->duration_minutes / 60, 1);
    }

    public function getWorkingMinutesAttribute(): int
    {
        return $this->duration_minutes - ($this->break_duration_minutes ?? 0);
    }

    public function getWorkingHoursAttribute(): float
    {
        return round($this->working_minutes / 60, 2);
    }

    public function scopeAssigned($query)
    {
        return $query->whereNotNull('user_id');
    }

    public function scopeUnassigned($query)
    {
        return $query->whereNull('user_id');
    }

    public function scopeForDate($query, $date)
    {
        return $query->whereDate('date', $date);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->whereBetween('date', [$startDate, $endDate]);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForStatus($query, ShiftStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', ShiftStatus::Draft);
    }

    public function scopePublished($query)
    {
        return $query->where('status', ShiftStatus::Published);
    }

    public function scopeVisibleToUser($query, User $user)
    {
        if ($user->isEmployee()) {
            return $query->where('status', '!=', ShiftStatus::Draft);
        }

        return $query;
    }

    public function isDraft(): bool
    {
        return $this->status === ShiftStatus::Draft;
    }

    public function isPublished(): bool
    {
        return $this->status === ShiftStatus::Published;
    }

    public function canBePublished(): bool
    {
        return $this->isDraft();
    }

    /**
     * Check if this shift is a recurring parent (template).
     */
    public function isRecurringParent(): bool
    {
        return $this->is_recurring && $this->parent_shift_id === null;
    }

    /**
     * Check if this shift is a child of a recurring parent.
     */
    public function isRecurringChild(): bool
    {
        return $this->parent_shift_id !== null;
    }

    /**
     * Check if this shift has child instances.
     */
    public function hasChildren(): bool
    {
        return $this->childShifts()->exists();
    }

    /**
     * Get future child shifts (from today onwards).
     *
     * @return Collection<int, Shift>
     */
    public function getFutureChildren(): Collection
    {
        return $this->childShifts()
            ->where('date', '>=', Carbon::today())
            ->orderBy('date')
            ->get();
    }

    /**
     * Scope to get only recurring parent shifts.
     *
     * @param  Builder<Shift>  $query
     * @return Builder<Shift>
     */
    public function scopeRecurringParents(Builder $query): Builder
    {
        return $query->where('is_recurring', true)
            ->whereNull('parent_shift_id');
    }

    /**
     * Scope to get only recurring child shifts.
     *
     * @param  Builder<Shift>  $query
     * @return Builder<Shift>
     */
    public function scopeRecurringChildren(Builder $query): Builder
    {
        return $query->whereNotNull('parent_shift_id');
    }

    /**
     * Get the recurrence frequency label.
     */
    public function getRecurrenceFrequencyLabelAttribute(): ?string
    {
        if (! $this->is_recurring || empty($this->recurrence_rule)) {
            return null;
        }

        $rule = $this->recurrence_rule;
        $frequency = $rule['frequency'] ?? null;
        $interval = $rule['interval'] ?? 1;

        return match ($frequency) {
            'daily' => $interval === 1 ? 'Daily' : "Every {$interval} days",
            'weekly' => $this->formatWeeklyLabel($rule, $interval),
            'monthly' => $interval === 1 ? 'Monthly' : "Every {$interval} months",
            default => null,
        };
    }

    /**
     * Format the weekly recurrence label.
     *
     * @param  array<string, mixed>  $rule
     */
    protected function formatWeeklyLabel(array $rule, int $interval): string
    {
        $daysOfWeek = $rule['days_of_week'] ?? [];

        if (empty($daysOfWeek)) {
            return $interval === 1 ? 'Weekly' : "Every {$interval} weeks";
        }

        $dayNames = ['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'];
        $selectedDays = array_map(fn ($day) => $dayNames[$day], $daysOfWeek);

        $daysStr = implode(', ', $selectedDays);

        return $interval === 1 ? "Weekly on {$daysStr}" : "Every {$interval} weeks on {$daysStr}";
    }
}
