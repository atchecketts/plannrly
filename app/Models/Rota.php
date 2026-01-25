<?php

namespace App\Models;

use App\Enums\RotaStatus;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Rota extends Model
{
    use BelongsToTenant;
    use HasFactory;
    use SoftDeletes;

    protected $fillable = [
        'tenant_id',
        'location_id',
        'department_id',
        'name',
        'start_date',
        'end_date',
        'status',
        'published_at',
        'published_by',
        'created_by',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'status' => RotaStatus::class,
            'published_at' => 'datetime',
        ];
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function publishedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'published_by');
    }

    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function shifts(): HasMany
    {
        return $this->hasMany(Shift::class);
    }

    public function publish(User $user): void
    {
        $this->update([
            'status' => RotaStatus::Published,
            'published_at' => now(),
            'published_by' => $user->id,
        ]);
    }

    public function archive(): void
    {
        $this->update(['status' => RotaStatus::Archived]);
    }

    public function isPublished(): bool
    {
        return $this->status === RotaStatus::Published;
    }

    public function isDraft(): bool
    {
        return $this->status === RotaStatus::Draft;
    }

    public function scopePublished($query)
    {
        return $query->where('status', RotaStatus::Published);
    }

    public function scopeDraft($query)
    {
        return $query->where('status', RotaStatus::Draft);
    }

    public function scopeForDateRange($query, $startDate, $endDate)
    {
        return $query->where('start_date', '<=', $endDate)
            ->where('end_date', '>=', $startDate);
    }
}
