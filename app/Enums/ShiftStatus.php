<?php

namespace App\Enums;

enum ShiftStatus: string
{
    case Draft = 'draft';
    case Published = 'published';
    case InProgress = 'in_progress';
    case Completed = 'completed';
    case Missed = 'missed';
    case Cancelled = 'cancelled';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Draft',
            self::Published => 'Published',
            self::InProgress => 'In Progress',
            self::Completed => 'Completed',
            self::Missed => 'Missed',
            self::Cancelled => 'Cancelled',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Draft => 'gray',
            self::Published => 'blue',
            self::InProgress => 'yellow',
            self::Completed => 'green',
            self::Missed => 'red',
            self::Cancelled => 'gray',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::Published, self::InProgress]);
    }

    public function isFinal(): bool
    {
        return in_array($this, [self::Completed, self::Missed, self::Cancelled]);
    }

    public function isDraft(): bool
    {
        return $this === self::Draft;
    }

    public function isPublished(): bool
    {
        return $this === self::Published;
    }

    public function isVisibleToEmployee(): bool
    {
        return ! $this->isDraft();
    }
}
