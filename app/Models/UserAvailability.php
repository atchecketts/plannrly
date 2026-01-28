<?php

namespace App\Models;

use App\Enums\AvailabilityType;
use App\Enums\PreferenceLevel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserAvailability extends Model
{
    use HasFactory;

    protected $table = 'user_availability';

    protected $fillable = [
        'user_id',
        'type',
        'day_of_week',
        'specific_date',
        'start_time',
        'end_time',
        'is_available',
        'preference_level',
        'notes',
        'effective_from',
        'effective_until',
    ];

    protected function casts(): array
    {
        return [
            'type' => AvailabilityType::class,
            'day_of_week' => 'integer',
            'specific_date' => 'date',
            'start_time' => 'datetime:H:i',
            'end_time' => 'datetime:H:i',
            'is_available' => 'boolean',
            'preference_level' => PreferenceLevel::class,
            'effective_from' => 'date',
            'effective_until' => 'date',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if this availability rule applies to the given date.
     */
    public function appliesToDate(Carbon $date): bool
    {
        if (! $this->isEffective($date)) {
            return false;
        }

        if ($this->type === AvailabilityType::SpecificDate) {
            return $this->specific_date?->isSameDay($date);
        }

        return $this->day_of_week === $date->dayOfWeek;
    }

    /**
     * Check if this availability rule is effective on the given date.
     */
    public function isEffective(Carbon $date): bool
    {
        if ($this->effective_from && $date->lt($this->effective_from)) {
            return false;
        }

        if ($this->effective_until && $date->gt($this->effective_until)) {
            return false;
        }

        return true;
    }

    /**
     * Check if a given time falls within this availability window.
     */
    public function coversTime(string $time): bool
    {
        if (! $this->start_time || ! $this->end_time) {
            return true;
        }

        $checkTime = Carbon::createFromFormat('H:i', $time);
        $startTime = Carbon::createFromFormat('H:i', $this->start_time->format('H:i'));
        $endTime = Carbon::createFromFormat('H:i', $this->end_time->format('H:i'));

        return $checkTime->gte($startTime) && $checkTime->lte($endTime);
    }

    /**
     * Get the day name for recurring availability.
     */
    public function getDayNameAttribute(): ?string
    {
        if ($this->day_of_week === null) {
            return null;
        }

        $days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        return $days[$this->day_of_week] ?? null;
    }

    /**
     * Get a formatted time range string.
     */
    public function getTimeRangeAttribute(): string
    {
        if (! $this->start_time || ! $this->end_time) {
            return 'All day';
        }

        return $this->start_time->format('H:i').' - '.$this->end_time->format('H:i');
    }

    /**
     * Scope to find recurring availability rules.
     */
    public function scopeRecurring($query)
    {
        return $query->where('type', AvailabilityType::Recurring);
    }

    /**
     * Scope to find specific date exceptions.
     */
    public function scopeSpecificDate($query)
    {
        return $query->where('type', AvailabilityType::SpecificDate);
    }

    /**
     * Scope to find availability for a specific day of week.
     */
    public function scopeForDayOfWeek($query, int $dayOfWeek)
    {
        return $query->where('day_of_week', $dayOfWeek);
    }

    /**
     * Scope to find availability for a specific date.
     */
    public function scopeForSpecificDate($query, Carbon $date)
    {
        return $query->where('specific_date', $date->toDateString());
    }

    /**
     * Scope to find currently effective rules.
     */
    public function scopeEffective($query, ?Carbon $date = null)
    {
        $date = $date ?? now();

        return $query->where(function ($q) use ($date) {
            $q->whereNull('effective_from')
                ->orWhere('effective_from', '<=', $date);
        })->where(function ($q) use ($date) {
            $q->whereNull('effective_until')
                ->orWhere('effective_until', '>=', $date);
        });
    }

    /**
     * Scope to find unavailable entries.
     */
    public function scopeUnavailable($query)
    {
        return $query->where('is_available', false)
            ->orWhere('preference_level', PreferenceLevel::Unavailable);
    }

    /**
     * Scope to find preferred time entries.
     */
    public function scopePreferred($query)
    {
        return $query->where('preference_level', PreferenceLevel::Preferred);
    }
}
