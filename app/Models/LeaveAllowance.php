<?php

namespace App\Models;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LeaveAllowance extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'leave_type_id',
        'year',
        'total_days',
        'used_days',
        'carried_over_days',
    ];

    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'total_days' => 'decimal:2',
            'used_days' => 'decimal:2',
            'carried_over_days' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function leaveType(): BelongsTo
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function getRemainingDaysAttribute(): float
    {
        return ($this->total_days + $this->carried_over_days) - $this->used_days;
    }

    public function getTotalAvailableAttribute(): float
    {
        return $this->total_days + $this->carried_over_days;
    }

    public function hasAvailableDays(float $days): bool
    {
        return $this->remaining_days >= $days;
    }

    public function deductDays(float $days): void
    {
        $this->increment('used_days', $days);
    }

    public function restoreDays(float $days): void
    {
        $this->decrement('used_days', $days);
    }

    public function scopeForYear($query, int $year)
    {
        return $query->where('year', $year);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForLeaveType($query, int $leaveTypeId)
    {
        return $query->where('leave_type_id', $leaveTypeId);
    }
}
