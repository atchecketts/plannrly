<?php

namespace App\Enums;

enum BillingCycle: string
{
    case Monthly = 'monthly';
    case Annual = 'annual';

    public function label(): string
    {
        return match ($this) {
            self::Monthly => 'Monthly',
            self::Annual => 'Annual',
        };
    }

    public function description(): string
    {
        return match ($this) {
            self::Monthly => 'Billed every month',
            self::Annual => 'Billed once per year (save up to 17%)',
        };
    }

    public function intervalInDays(): int
    {
        return match ($this) {
            self::Monthly => 30,
            self::Annual => 365,
        };
    }
}
