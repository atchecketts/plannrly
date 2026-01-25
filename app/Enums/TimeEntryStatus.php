<?php

namespace App\Enums;

enum TimeEntryStatus: string
{
    case ClockedIn = 'clocked_in';
    case OnBreak = 'on_break';
    case ClockedOut = 'clocked_out';

    public function label(): string
    {
        return match ($this) {
            self::ClockedIn => 'Clocked In',
            self::OnBreak => 'On Break',
            self::ClockedOut => 'Clocked Out',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::ClockedIn => 'green',
            self::OnBreak => 'yellow',
            self::ClockedOut => 'gray',
        };
    }

    public function isActive(): bool
    {
        return in_array($this, [self::ClockedIn, self::OnBreak]);
    }
}
