<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Notification extends Model
{
    use SoftDeletes, HasFactory;

    const CHANNEL_EMAIL  = 'email';
    const CHANNEL_SYSTEM = 'system';
    const CHANNEL_PUSH   = 'push';

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
    // Helpers
    // ---------------------------------------------------------

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

    public function scopeUnread($query)
    {
        return $query->whereNull('read_at');
    }

    public function scopeForTeamInvite($query)
    {
        return $query->where('type', 'team_invite');
    }

    public function getTeamIdAttribute(): ?int
    {
        return $this->data_json['team_id'] ?? null;
    }

    public function scopeForTeam($query, $teamId)
    {
        return $query->whereJsonContains('data_json->team_id', $teamId);
    }

    public function scopeForUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function isActionable(): bool
    {
        return $this->type === 'team_invite';
    }
}
