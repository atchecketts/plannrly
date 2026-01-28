<?php

namespace App\Enums;

enum AvailabilityType: string
{
    case Recurring = 'recurring';
    case SpecificDate = 'specific_date';

    public function label(): string
    {
        return match ($this) {
            self::Recurring => 'Recurring Weekly',
            self::SpecificDate => 'Specific Date',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Recurring => 'Repeats every week on the same day',
            self::SpecificDate => 'Applies only to a specific date',
        };
    }

    public function isRecurring(): bool
    {
        return $this === self::Recurring;
    }

    public function isSpecificDate(): bool
    {
        return $this === self::SpecificDate;
    }
}
