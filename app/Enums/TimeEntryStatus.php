<?php

namespace App\Enums;

enum TimeEntryStatus: string
{
    case ClockedIn = 'clocked_in';
    case OnBreak = 'on_break';
    case ClockedOut = 'clocked_out';
    case Missed = 'missed';

    public function label(): string
    {
        return match ($this) {
            self::ClockedIn => 'Clocked In',
            self::OnBreak => 'On Break',
            self::ClockedOut => 'Clocked Out',
            self::Missed => 'Missed',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ClockedIn => 'green',
            self::OnBreak => 'yellow',
            self::ClockedOut => 'gray',
            self::Missed => 'red',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::ClockedIn, self::OnBreak]);
    }

    public function isMissed(): bool
    {
        return $this === self::Missed;
    }
}
