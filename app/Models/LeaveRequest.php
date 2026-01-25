<?php

namespace App\Models;

use App\Enums\LeaveRequestStatus;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class LeaveRequest extends Model
{
    use BelongsToTenant;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'user_id',
        'leave_type_id',
        'start_date',
        'end_date',
        'start_half_day',
        'end_half_day',
        'total_days',
        'reason',
        'status',
        'reviewed_by',
        'reviewed_at',
        'review_notes',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'start_half_day' => 'boolean',
            'end_half_day' => 'boolean',
            'total_days' => 'decimal:2',
            'status' => LeaveRequestStatus::class,
            'reviewed_at' => 'datetime',
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

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function calculateTotalDays(): float
    {
        $days = $this->start_date->diffInDays($this->end_date) + 1;

        if ($this->start_half_day) {
            $days -= 0.5;
        }

        if ($this->end_half_day && ! $this->start_date->equalTo($this->end_date)) {
            $days -= 0.5;
        }

        return $days;
    }

    public function submit(): void
    {
        $this->update(['status' => LeaveRequestStatus::Requested]);
    }

    public function approve(User $reviewer, ?string $notes = null): void
    {
        $this->update([
            'status' => LeaveRequestStatus::Approved,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    public function reject(User $reviewer, ?string $notes = null): void
    {
        $this->update([
            'status' => LeaveRequestStatus::Rejected,
            'reviewed_by' => $reviewer->id,
            'reviewed_at' => now(),
            'review_notes' => $notes,
        ]);
    }

    public function isPending(): bool
    {
        return $this->status === LeaveRequestStatus::Requested;
    }

    public function isApproved(): bool
    {
        return $this->status === LeaveRequestStatus::Approved;
    }

    public function isRejected(): bool
    {
        return $this->status === LeaveRequestStatus::Rejected;
    }

    public function isDraft(): bool
    {
        return $this->status === LeaveRequestStatus::Draft;
    }

    public function scopePending($query)
    {
        return $query->where('status', LeaveRequestStatus::Requested);
    }

    public function scopeApproved($query)
    {
        return $query->where('status', LeaveRequestStatus::Approved);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate);
    }

    public function scopeOverlapping($query, $startDate, $endDate, ?int $excludeId = null)
    {
        return $query->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate)
            ->when($excludeId, fn ($q) => $q->where('id', '!=', $excludeId));
    }
}
