<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
    protected $fillable = [
        'user_id',
        'notification_type',
        'email_enabled',
        'push_enabled',
        'in_app_enabled',
    ];

    protected function casts(): array
    {
        return [
            'email_enabled' => 'boolean',
            'push_enabled' => 'boolean',
            'in_app_enabled' => 'boolean',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function isEmailEnabled(): bool
    {
        return $this->email_enabled;
    }

    public function isPushEnabled(): bool
    {
        return $this->push_enabled;
    }

    public function isInAppEnabled(): bool
    {
        return $this->in_app_enabled;
    }

    public function scopeForType($query, string $type)
    {
        return $query->where('notification_type', $type);
    }

    public function scopeEmailEnabled($query)
    {
        return $query->where('email_enabled', true);
    }

    public function scopePushEnabled($query)
    {
        return $query->where('push_enabled', true);
    }
}
