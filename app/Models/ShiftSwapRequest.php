<?php

namespace App\Models;

use App\Enums\SwapRequestStatus;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ShiftSwapRequest extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'requesting_user_id',
        'target_user_id',
        'requesting_shift_id',
        'target_shift_id',
        'reason',
        'status',
        'responded_at',
        'approved_by',
        'approved_at',
    ];

    protected function casts(): array
    {
        return [
            'status' => SwapRequestStatus::class,
            'responded_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function requestingUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'requesting_user_id');
    }

    public function targetUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_user_id');
    }

    public function requestingShift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'requesting_shift_id');
    }

    public function targetShift(): BelongsTo
    {
        return $this->belongsTo(Shift::class, 'target_shift_id');
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function accept(): void
    {
        $this->update([
            'status' => SwapRequestStatus::Accepted,
            'responded_at' => now(),
        ]);
    }

    public function reject(): void
    {
        $this->update([
            'status' => SwapRequestStatus::Rejected,
            'responded_at' => now(),
        ]);
    }

    public function cancel(): void
    {
        $this->update(['status' => SwapRequestStatus::Cancelled]);
    }

    public function adminApprove(User $approver): void
    {
        $this->update([
            'approved_by' => $approver->id,
            'approved_at' => now(),
        ]);

        $this->executeSwap();
    }

    protected function executeSwap(): void
    {
        $requestingShift = $this->requestingShift;
        $targetShift = $this->targetShift;

        if ($targetShift) {
            $requestingShift->update(['user_id' => $this->target_user_id]);
            $targetShift->update(['user_id' => $this->requesting_user_id]);
        } else {
            $requestingShift->update(['user_id' => $this->target_user_id]);
        }
    }

    public function isPending(): bool
    {
        return $this->status === SwapRequestStatus::Pending;
    }

    public function isAccepted(): bool
    {
        return $this->status === SwapRequestStatus::Accepted;
    }

    public function scopePending($query)
    {
        return $query->where('status', SwapRequestStatus::Pending);
    }

    public function scopeAccepted($query)
    {
        return $query->where('status', SwapRequestStatus::Accepted);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where(function ($q) use ($userId) {
            $q->where('requesting_user_id', $userId)
                ->orWhere('target_user_id', $userId);
        });
    }

    public function scopeAwaitingApproval($query)
    {
        return $query->where('status', SwapRequestStatus::Accepted)
            ->whereNull('approved_by');
    }
}
