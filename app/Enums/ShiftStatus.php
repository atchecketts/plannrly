<?php

namespace App\Enums;

enum ShiftStatus: string
{
    case Scheduled = 'scheduled';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Missed = 'missed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Scheduled => 'Scheduled',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Missed => 'Missed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Scheduled => 'blue',
            self::InProgress => 'yellow',
            self::Completed => 'green',
            self::Missed => 'red',
            self::Cancelled => 'gray',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Scheduled, self::InProgress]);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Missed, self::Cancelled]);
    }
}
