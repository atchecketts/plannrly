<?php

namespace App\Enums;

enum EmploymentStatus: string
{
    case Active = 'active';
    case OnLeave = 'on_leave';
    case Suspended = 'suspended';
    case NoticePeriod = 'notice_period';
    case Terminated = 'terminated';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::OnLeave => 'On Leave',
            self::Suspended => 'Suspended',
            self::NoticePeriod => 'Notice Period',
            self::Terminated => 'Terminated',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::OnLeave => 'blue',
            self::Suspended => 'yellow',
            self::NoticePeriod => 'orange',
            self::Terminated => 'red',
        };
    }

    public function isActive(): bool
    {
        return $this === self::Active;
    }

    public function canWork(): bool
    {
        return in_array($this, [self::Active, self::NoticePeriod]);
    }

    public function isLeaving(): bool
    {
        return $this === self::NoticePeriod;
    }
}
