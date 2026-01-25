<?php

namespace App\Enums;

enum LeaveRequestStatus: string
{
    case Draft = 'draft';
    case Requested = 'requested';
    case Approved = 'approved';
    case Rejected = 'rejected';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Requested => 'Pending Approval',
            self::Approved => 'Approved',
            self::Rejected => 'Rejected',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Requested => 'yellow',
            self::Approved => 'green',
            self::Rejected => 'red',
        };
    }

    public function isPending(): bool
    {
        return $this === self::Requested;
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Approved, self::Rejected]);
    }

    public function canBeEdited(): bool
    {
        return $this === self::Draft;
    }

    public function canBeCancelled(): bool
    {
        return in_array($this, [self::Draft, self::Requested]);
    }
}
