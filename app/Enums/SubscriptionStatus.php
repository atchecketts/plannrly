<?php

namespace App\Enums;

enum SubscriptionStatus: string
{
    case Active = 'active';
    case PastDue = 'past_due';
    case Cancelled = 'cancelled';
    case Trialing = 'trialing';

    public function label(): string
    {
        return match ($this) {
            self::Active => 'Active',
            self::PastDue => 'Past Due',
            self::Cancelled => 'Cancelled',
            self::Trialing => 'Trialing',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Active => 'green',
            self::PastDue => 'yellow',
            self::Cancelled => 'red',
            self::Trialing => 'blue',
        };
    }

    /**
     * Determine if this status allows access to subscription features.
     */
    public function isAccessible(): bool
    {
        return in_array($this, [
            self::Active,
            self::PastDue,
            self::Trialing,
        ], true);
    }
}
