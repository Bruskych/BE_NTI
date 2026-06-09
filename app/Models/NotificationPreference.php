<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Модель настроек уведомлений пользователя по каналам */
class NotificationPreference extends Model
{
    use HasFactory;

    // ---------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------

    protected $fillable = [
        'user_id',
        'email_enabled',
        'system_enabled',
        'marketing_enabled',
        'deadline_alerts_enabled',
    ];

    protected $casts = [
        'email_enabled'           => 'boolean',
        'system_enabled'          => 'boolean',
        'marketing_enabled'       => 'boolean',
        'deadline_alerts_enabled' => 'boolean',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
