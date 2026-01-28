<?php

namespace App\Enums;

enum PreferenceLevel: string
{
    case Preferred = 'preferred';
    case Available = 'available';
    case IfNeeded = 'if_needed';
    case Unavailable = 'unavailable';

    public function label(): string
    {
        return match ($this) {
            self::Preferred => 'Preferred',
            self::Available => 'Available',
            self::IfNeeded => 'If Needed',
            self::Unavailable => 'Unavailable',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Preferred => 'green',
            self::Available => 'blue',
            self::IfNeeded => 'yellow',
            self::Unavailable => 'red',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Preferred => 'This is my preferred time to work',
            self::Available => 'I can work during this time',
            self::IfNeeded => 'I can work if absolutely necessary',
            self::Unavailable => 'I cannot work during this time',
        };
    }

    public function priority(): int
    {
        return match ($this) {
            self::Preferred => 1,
            self::Available => 2,
            self::IfNeeded => 3,
            self::Unavailable => 4,
        };
    }

    public function canSchedule(): bool
    {
        return $this !== self::Unavailable;
    }

    public function isPreferred(): bool
    {
        return $this === self::Preferred;
    }

    public function isUnavailable(): bool
    {
        return $this === self::Unavailable;
    }
}
