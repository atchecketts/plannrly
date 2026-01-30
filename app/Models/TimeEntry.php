<?php

namespace App\Models;

use App\Enums\TimeEntryStatus;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'shift_id',
        'clock_in_at',
        'clock_out_at',
        'break_start_at',
        'break_end_at',
        'actual_break_minutes',
        'notes',
        'clock_in_location',
        'clock_out_location',
        'status',
        'approved_by',
        'approved_at',
        'adjustment_reason',
    ];

    protected function casts(): array
    {
        return [
            'clock_in_at' => 'datetime',
            'clock_out_at' => 'datetime',
            'break_start_at' => 'datetime',
            'break_end_at' => 'datetime',
            'clock_in_location' => 'array',
            'clock_out_location' => 'array',
            'status' => TimeEntryStatus::class,
            'approved_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function clockIn(?array $location = null): void
    {
        $this->update([
            'clock_in_at' => now(),
            'clock_in_location' => $location,
            'status' => TimeEntryStatus::ClockedIn,
        ]);
    }

    public function clockOut(?array $location = null): void
    {
        $this->update([
            'clock_out_at' => now(),
            'clock_out_location' => $location,
            'status' => TimeEntryStatus::ClockedOut,
        ]);
    }

    public function startBreak(): void
    {
        $this->update([
            'break_start_at' => now(),
            'status' => TimeEntryStatus::OnBreak,
        ]);
    }

    public function endBreak(): void
    {
        $breakMinutes = $this->break_start_at->diffInMinutes(now());
        $totalBreak = ($this->actual_break_minutes ?? 0) + $breakMinutes;

        $this->update([
            'break_end_at' => now(),
            'actual_break_minutes' => $totalBreak,
            'status' => TimeEntryStatus::ClockedIn,
        ]);
    }

    public function isApproved(): bool
    {
        return $this->approved_at !== null;
    }

    public function requiresApproval(): bool
    {
        if ($this->isApproved()) {
            return false;
        }

        $settings = TenantSettings::where('tenant_id', $this->tenant_id)->first();

        return $settings?->require_manager_approval ?? false;
    }

    public function approve(User $approver): void
    {
        $this->update([
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);
    }

    public function getTotalWorkedMinutesAttribute(): ?int
    {
        if (! $this->clock_in_at) {
            return null;
        }

        $end = $this->clock_out_at ?? now();
        $total = $this->clock_in_at->diffInMinutes($end);

        return $total - ($this->actual_break_minutes ?? 0);
    }

    public function getTotalWorkedHoursAttribute(): ?float
    {
        $minutes = $this->total_worked_minutes;

        return $minutes !== null ? round($minutes / 60, 2) : null;
    }

    /**
     * Get the scheduled duration in minutes from the associated shift.
     */
    public function getScheduledDurationMinutesAttribute(): ?int
    {
        return $this->shift?->working_minutes;
    }

    /**
     * Get the variance between actual and scheduled duration.
     * Positive = overtime, Negative = undertime.
     */
    public function getVarianceMinutesAttribute(): ?int
    {
        $actual = $this->total_worked_minutes;
        $scheduled = $this->scheduled_duration_minutes;

        if ($actual === null || $scheduled === null) {
            return null;
        }

        return $actual - $scheduled;
    }

    /**
     * Get the clock-in variance in minutes.
     * Positive = late arrival, Negative = early arrival.
     */
    public function getClockInVarianceMinutesAttribute(): ?int
    {
        if (! $this->clock_in_at || ! $this->shift?->start_time) {
            return null;
        }

        // Build scheduled start datetime using shift date and start time
        $scheduledStart = $this->shift->date->copy()
            ->setTimeFrom($this->shift->start_time);

        return $scheduledStart->diffInMinutes($this->clock_in_at, false);
    }

    /**
     * Get the clock-out variance in minutes.
     * Positive = stayed late, Negative = left early.
     */
    public function getClockOutVarianceMinutesAttribute(): ?int
    {
        if (! $this->clock_out_at || ! $this->shift?->end_time) {
            return null;
        }

        // Build scheduled end datetime using shift date and end time
        $scheduledEnd = $this->shift->date->copy()
            ->setTimeFrom($this->shift->end_time);

        // Handle overnight shifts
        if ($this->shift->isOvernightShift()) {
            $scheduledEnd->addDay();
        }

        return $scheduledEnd->diffInMinutes($this->clock_out_at, false);
    }

    /**
     * Check if the employee arrived late (beyond grace period).
     */
    public function getIsLateAttribute(): bool
    {
        $variance = $this->clock_in_variance_minutes;

        if ($variance === null) {
            return false;
        }

        $gracePeriod = $this->getGracePeriodMinutes();

        return $variance > $gracePeriod;
    }

    /**
     * Check if the employee left early (before scheduled end).
     */
    public function getIsEarlyDepartureAttribute(): bool
    {
        $variance = $this->clock_out_variance_minutes;

        if ($variance === null) {
            return false;
        }

        // Left more than 5 minutes early
        return $variance < -5;
    }

    /**
     * Check if the employee worked overtime.
     */
    public function getIsOvertimeAttribute(): bool
    {
        $variance = $this->variance_minutes;

        if ($variance === null) {
            return false;
        }

        // Worked at least 15 minutes overtime
        return $variance >= 15;
    }

    /**
     * Check if this is a no-show (shift passed without clock-in).
     */
    public function getIsNoShowAttribute(): bool
    {
        // If clocked in, not a no-show
        if ($this->clock_in_at) {
            return false;
        }

        if (! $this->shift) {
            return false;
        }

        // Build scheduled end datetime
        $scheduledEnd = $this->shift->date->copy()
            ->setTimeFrom($this->shift->end_time);

        if ($this->shift->isOvernightShift()) {
            $scheduledEnd->addDay();
        }

        // If shift has ended and no clock-in, it's a no-show
        return now()->gt($scheduledEnd);
    }

    /**
     * Get the clock-in variance status for display.
     *
     * @return array{label: string, color: string}
     */
    public function getClockInStatusAttribute(): array
    {
        $variance = $this->clock_in_variance_minutes;

        if ($variance === null) {
            return ['label' => 'N/A', 'color' => 'gray'];
        }

        $gracePeriod = $this->getGracePeriodMinutes();

        if ($variance < -5) {
            return ['label' => abs($variance).'m early', 'color' => 'blue'];
        }

        if ($variance <= $gracePeriod) {
            return ['label' => 'On time', 'color' => 'green'];
        }

        if ($variance <= 15) {
            return ['label' => $variance.'m late', 'color' => 'yellow'];
        }

        return ['label' => $variance.'m late', 'color' => 'red'];
    }

    /**
     * Get the clock-out variance status for display.
     *
     * @return array{label: string, color: string}
     */
    public function getClockOutStatusAttribute(): array
    {
        $variance = $this->clock_out_variance_minutes;

        if ($variance === null) {
            return ['label' => 'N/A', 'color' => 'gray'];
        }

        if ($variance < -15) {
            return ['label' => abs($variance).'m early', 'color' => 'red'];
        }

        if ($variance < -5) {
            return ['label' => abs($variance).'m early', 'color' => 'yellow'];
        }

        if ($variance <= 5) {
            return ['label' => 'On time', 'color' => 'green'];
        }

        return ['label' => $variance.'m overtime', 'color' => 'orange'];
    }

    /**
     * Get the overall variance status for display.
     *
     * @return array{label: string, color: string}
     */
    public function getVarianceStatusAttribute(): array
    {
        $variance = $this->variance_minutes;

        if ($variance === null) {
            return ['label' => 'N/A', 'color' => 'gray'];
        }

        if ($variance < -30) {
            return ['label' => $this->formatVariance($variance), 'color' => 'red'];
        }

        if ($variance < -5) {
            return ['label' => $this->formatVariance($variance), 'color' => 'yellow'];
        }

        if ($variance <= 5) {
            return ['label' => 'On target', 'color' => 'green'];
        }

        if ($variance <= 30) {
            return ['label' => $this->formatVariance($variance), 'color' => 'yellow'];
        }

        return ['label' => $this->formatVariance($variance), 'color' => 'orange'];
    }

    /**
     * Format variance minutes for display.
     */
    public function formatVariance(int $minutes): string
    {
        $absMinutes = abs($minutes);
        $hours = intdiv($absMinutes, 60);
        $mins = $absMinutes % 60;

        $formatted = $hours > 0 ? "{$hours}h {$mins}m" : "{$mins}m";

        if ($minutes > 0) {
            return '+'.$formatted;
        }

        if ($minutes < 0) {
            return '-'.$formatted;
        }

        return $formatted;
    }

    /**
     * Get the grace period in minutes from tenant settings.
     */
    protected function getGracePeriodMinutes(): int
    {
        $settings = TenantSettings::where('tenant_id', $this->tenant_id)->first();

        return $settings?->clock_in_grace_minutes ?? 15;
    }

    public function isClockedIn(): bool
    {
        return $this->status === TimeEntryStatus::ClockedIn;
    }

    public function isOnBreak(): bool
    {
        return $this->status === TimeEntryStatus::OnBreak;
    }

    public function isClockedOut(): bool
    {
        return $this->status === TimeEntryStatus::ClockedOut;
    }

    public function isMissed(): bool
    {
        return $this->status === TimeEntryStatus::Missed;
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            TimeEntryStatus::ClockedIn,
            TimeEntryStatus::OnBreak,
        ]);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePendingApproval($query)
    {
        return $query->whereNull('approved_at')
            ->where('status', TimeEntryStatus::ClockedOut);
    }

    public function scopeMissed($query)
    {
        return $query->where('status', TimeEntryStatus::Missed);
    }

    public function scopeForShift($query, int $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }
}
