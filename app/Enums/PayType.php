<?php

namespace App\Enums;

enum PayType: string
{
    case Hourly = 'hourly';
    case Salaried = 'salaried';

    public function label(): string
    {
        return match ($this) {
            self::Hourly => 'Hourly',
            self::Salaried => 'Salaried',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Hourly => 'Paid per hour worked',
            self::Salaried => 'Fixed annual salary',
        };
    }

    public function isHourly(): bool
    {
        return $this === self::Hourly;
    }

    public function isSalaried(): bool
    {
        return $this === self::Salaried;
    }
}
