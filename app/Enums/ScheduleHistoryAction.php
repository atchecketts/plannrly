<?php

namespace App\Enums;

enum ScheduleHistoryAction: string
{
    case Created = 'created';
    case Updated = 'updated';
    case Deleted = 'deleted';

    public function label(): string
    {
        return match ($this) {
            self::Created => 'Created',
            self::Updated => 'Updated',
            self::Deleted => 'Deleted',
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::Created => 'green',
            self::Updated => 'blue',
            self::Deleted => 'red',
        };
    }

    public function icon(): string
    {
        return match ($this) {
            self::Created => 'plus-circle',
            self::Updated => 'pencil',
            self::Deleted => 'trash',
        };
    }
}
