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
        ];
    }

    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }
}
