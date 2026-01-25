<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserFilterDefault extends Model
{
    protected $fillable = [
        'user_id',
        'filter_context',
        'location_id',
        'department_id',
        'business_role_id',
        'additional_filters',
    ];

    protected function casts(): array
    {
        return [
            'additional_filters' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(Location::class);
    }

    public function department(): BelongsTo
    {
        return $this->belongsTo(Department::class);
    }

    public function businessRole(): BelongsTo
    {
        return $this->belongsTo(BusinessRole::class);
    }

    public function getFilter(string $key, mixed $default = null): mixed
    {
        return data_get($this->additional_filters, $key, $default);
    }

    public function setFilter(string $key, mixed $value): void
    {
        $filters = $this->additional_filters ?? [];
        data_set($filters, $key, $value);
        $this->additional_filters = $filters;
    }

    public function scopeForContext($query, string $context)
    {
        return $query->where('filter_context', $context);
    }
}
