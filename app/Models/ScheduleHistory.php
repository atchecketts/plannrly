<?php

namespace App\Models;

use App\Enums\ScheduleHistoryAction;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScheduleHistory extends Model
{
    use BelongsToTenant;
    use HasFactory;

    protected $table = 'schedule_history';

    protected $fillable = [
        'tenant_id',
        'shift_id',
        'user_id',
        'action',
        'old_values',
        'new_values',
    ];

    protected function casts(): array
    {
        return [
            'action' => ScheduleHistoryAction::class,
            'old_values' => 'array',
            'new_values' => 'array',
        ];
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class)->withTrashed();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get human-readable summary of what changed.
     *
     * @return array<int, string>
     */
    public function getChangeSummary(): array
    {
        $changes = [];

        if ($this->action === ScheduleHistoryAction::Created) {
            $changes[] = 'Shift created';

            return $changes;
        }

        if ($this->action === ScheduleHistoryAction::Deleted) {
            $changes[] = 'Shift deleted';

            return $changes;
        }

        $old = $this->old_values ?? [];
        $new = $this->new_values ?? [];

        $fieldLabels = [
            'user_id' => 'Assigned user',
            'date' => 'Date',
            'start_time' => 'Start time',
            'end_time' => 'End time',
            'break_duration_minutes' => 'Break duration',
            'notes' => 'Notes',
            'status' => 'Status',
            'location_id' => 'Location',
            'department_id' => 'Department',
            'business_role_id' => 'Role',
        ];

        foreach ($new as $field => $newValue) {
            $oldValue = $old[$field] ?? null;

            if ($oldValue !== $newValue && isset($fieldLabels[$field])) {
                $changes[] = $fieldLabels[$field].' changed';
            }
        }

        return $changes ?: ['Shift updated'];
    }

    public function scopeForShift($query, int $shiftId)
    {
        return $query->where('shift_id', $shiftId);
    }

    public function scopeRecent($query, int $days = 30)
    {
        return $query->where('created_at', '>=', now()->subDays($days));
    }
}
