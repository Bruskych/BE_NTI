<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Модель системного уведомления пользователя с поддержкой приглашений в команду */
class Notification extends Model
{
    use SoftDeletes, HasFactory;

    // ---------------------------------------------------------
    // Constants
    // ---------------------------------------------------------

    const CHANNEL_EMAIL  = 'email';
    const CHANNEL_SYSTEM = 'system';
    const CHANNEL_PUSH   = 'push';

    // ---------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------

    protected $fillable = [
        'user_id',
        'type',
        'channel',
        'title',
        'message',
        'data_json',
        'read_at',
    ];

    protected $casts = [
        'data_json' => 'array',
        'read_at'   => 'datetime',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    // ---------------------------------------------------------
    // Query Scopes
    // ---------------------------------------------------------

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function scopeForTeamInvite(Builder $query): Builder
    {
        return $query->where('type', 'team_invite');
    }

    public function scopeForTeam(Builder $query, $teamId): Builder
    {
        return $query->whereJsonContains('data_json->team_id', $teamId);
    }

    public function scopeForUser(Builder $query, $userId): Builder
    {
        return $query->where('user_id', $userId);
    }

    // ---------------------------------------------------------
    // Accessors & Helpers
    // ---------------------------------------------------------

    public function getTeamIdAttribute(): ?int
    {
        return $this->data_json['team_id'] ?? null;
    }

    public function isRead(): bool
    {
        return $this->read_at !== null;
    }

    public function markAsRead(): void
    {
        $this->update(['read_at' => now()]);
    }

    public function isEmail(): bool
    {
        return $this->channel === self::CHANNEL_EMAIL;
    }

    public function isSystem(): bool
    {
        return $this->channel === self::CHANNEL_SYSTEM;
    }

    public function isActionable(): bool
    {
        return $this->type === 'team_invite';
    }
}
