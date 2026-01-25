<?php

namespace App\Models;

use App\Enums\ShiftStatus;
use App\Traits\BelongsToTenant;
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
        'rota_id',
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
        ];
    }

    public function rota(): BelongsTo
    {
        return $this->belongsTo(Rota::class);
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
            return $start->diffInMinutes($end);
        }

        return 0;
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
}
