<?php

namespace App\Models;

use App\Enums\EmploymentStatus;
use App\Enums\PayType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserEmploymentDetails extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'employment_start_date',
        'employment_end_date',
        'final_working_date',
        'probation_end_date',
        'employment_status',
        'pay_type',
        'base_hourly_rate',
        'annual_salary',
        'currency',
        'target_hours_per_week',
        'min_hours_per_week',
        'max_hours_per_week',
        'overtime_eligible',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'employment_start_date' => 'date',
            'employment_end_date' => 'date',
            'final_working_date' => 'date',
            'probation_end_date' => 'date',
            'employment_status' => EmploymentStatus::class,
            'pay_type' => PayType::class,
            'base_hourly_rate' => 'decimal:2',
            'annual_salary' => 'decimal:2',
            'target_hours_per_week' => 'decimal:2',
            'min_hours_per_week' => 'decimal:2',
            'max_hours_per_week' => 'decimal:2',
            'overtime_eligible' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if the employee is currently on probation.
     */
    public function isOnProbation(): bool
    {
        if (! $this->probation_end_date) {
            return false;
        }

        return $this->probation_end_date->isFuture();
    }

    /**
     * Check if the employee is leaving soon (within the specified days).
     */
    public function isLeavingSoon(int $withinDays = 30): bool
    {
        if (! $this->final_working_date) {
            return false;
        }

        return $this->final_working_date->isFuture()
            && $this->final_working_date->diffInDays(now()) <= $withinDays;
    }

    /**
     * Check if the employee has left.
     */
    public function hasLeft(): bool
    {
        if (! $this->final_working_date) {
            return false;
        }

        return $this->final_working_date->isPast();
    }

    /**
     * Get the calculated hourly rate from annual salary.
     * Assumes 52 weeks per year.
     */
    public function getCalculatedHourlyRateAttribute(): ?float
    {
        if (! $this->annual_salary || ! $this->target_hours_per_week) {
            return null;
        }

        return round($this->annual_salary / (52 * $this->target_hours_per_week), 2);
    }

    /**
     * Get the effective hourly rate for this user.
     * Returns base_hourly_rate for hourly workers, or calculated rate for salaried.
     */
    public function getEffectiveHourlyRateAttribute(): ?float
    {
        if ($this->pay_type === PayType::Hourly) {
            return $this->base_hourly_rate ? (float) $this->base_hourly_rate : null;
        }

        return $this->calculated_hourly_rate;
    }

    /**
     * Scope to find employees on notice period.
     */
    public function scopeOnNoticePeriod($query)
    {
        return $query->where('employment_status', EmploymentStatus::NoticePeriod);
    }

    /**
     * Scope to find employees leaving soon.
     */
    public function scopeLeavingSoon($query, int $withinDays = 30)
    {
        return $query->whereNotNull('final_working_date')
            ->where('final_working_date', '>', now())
            ->where('final_working_date', '<=', now()->addDays($withinDays));
    }

    /**
     * Scope to find active employees.
     */
    public function scopeActive($query)
    {
        return $query->where('employment_status', EmploymentStatus::Active);
    }

    /**
     * Scope to find employees who can work.
     */
    public function scopeCanWork($query)
    {
        return $query->whereIn('employment_status', [
            EmploymentStatus::Active->value,
            EmploymentStatus::NoticePeriod->value,
        ]);
    }
}
