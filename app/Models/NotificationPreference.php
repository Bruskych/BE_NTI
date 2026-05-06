<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class NotificationPreference extends Model
{
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
