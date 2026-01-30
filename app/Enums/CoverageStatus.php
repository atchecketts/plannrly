<?php

namespace App\Enums;

enum CoverageStatus: string
{
    case Adequate = 'adequate';
    case Understaffed = 'understaffed';
    case Overstaffed = 'overstaffed';
    case NoRequirement = 'no_requirement';

    public function label(): string
    {
        return match ($this) {
            self::Adequate => 'Adequate',
            self::Understaffed => 'Understaffed',
            self::Overstaffed => 'Overstaffed',
            self::NoRequirement => 'No Requirement',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Adequate => 'green',
            self::Understaffed => 'red',
            self::Overstaffed => 'yellow',
            self::NoRequirement => 'gray',
        };
    }

    /**
     * Get the Tailwind CSS classes for background and text colors.
     */
    public function colorClasses(): string
    {
        return match ($this) {
            self::Adequate => 'bg-green-500/10 text-green-400 ring-green-500/20',
            self::Understaffed => 'bg-red-500/10 text-red-400 ring-red-500/20',
            self::Overstaffed => 'bg-yellow-500/10 text-yellow-400 ring-yellow-500/20',
            self::NoRequirement => 'bg-gray-500/10 text-gray-400 ring-gray-500/20',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Adequate => 'check-circle',
            self::Understaffed => 'exclamation-triangle',
            self::Overstaffed => 'arrow-trending-up',
            self::NoRequirement => 'minus-circle',
        };
    }

    /**
     * Get an SVG path for the status icon.
     */
    public function iconSvg(): string
    {
        return match ($this) {
            self::Adequate => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
            self::Understaffed => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
            self::Overstaffed => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6" />',
            self::NoRequirement => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />',
        };
    }

    /**
     * Determine if this status indicates a problem.
     */
    public function hasProblem(): bool
    {
        return in_array($this, [self::Understaffed, self::Overstaffed]);
    }

    /**
     * Determine the severity level (higher = more severe).
     */
    public function severity(): int
    {
        return match ($this) {
            self::Understaffed => 3,
            self::Overstaffed => 2,
            self::Adequate => 1,
            self::NoRequirement => 0,
        };
    }
}
