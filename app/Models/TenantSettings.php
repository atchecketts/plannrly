<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TenantSettings extends Model
{
    use HasFactory;

    protected $fillable = [
        'tenant_id',
        'enable_clock_in_out',
        'enable_shift_acknowledgement',
        'day_starts_at',
        'day_ends_at',
        'week_starts_on',
        'timezone',
        'date_format',
        'time_format',
        'missed_grace_minutes',
        'notify_on_publish',
        'require_admin_approval_for_swaps',
        'leave_carryover_mode',
        'default_currency',
        'primary_color',
        'clock_in_grace_minutes',
        'require_gps_clock_in',
        'auto_clock_out_enabled',
        'auto_clock_out_time',
        'overtime_threshold_minutes',
        'require_manager_approval',
        'enable_shift_reminders',
        'remind_day_before',
        'remind_hours_before',
        'remind_hours_before_value',
    ];

    protected function casts(): array
    {
        return [
            'enable_clock_in_out' => 'boolean',
            'enable_shift_acknowledgement' => 'boolean',
            'day_starts_at' => 'datetime:H:i:s',
            'day_ends_at' => 'datetime:H:i:s',
            'week_starts_on' => 'integer',
            'missed_grace_minutes' => 'integer',
            'notify_on_publish' => 'boolean',
            'require_admin_approval_for_swaps' => 'boolean',
            'clock_in_grace_minutes' => 'integer',
            'require_gps_clock_in' => 'boolean',
            'auto_clock_out_enabled' => 'boolean',
            'overtime_threshold_minutes' => 'integer',
            'require_manager_approval' => 'boolean',
            'enable_shift_reminders' => 'boolean',
            'remind_day_before' => 'boolean',
            'remind_hours_before' => 'boolean',
            'remind_hours_before_value' => 'integer',
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
