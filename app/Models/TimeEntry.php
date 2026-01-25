<?php

namespace App\Models;

use App\Enums\TimeEntryStatus;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TimeEntry extends Model
{
    use BelongsToTenant;

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
}
