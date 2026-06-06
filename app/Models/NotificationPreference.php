<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property bool $email_enabled
 * @property bool $system_enabled
 * @property bool $marketing_enabled
 * @property bool $deadline_alerts_enabled
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereDeadlineAlertsEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereEmailEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereMarketingEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereSystemEnabled($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|NotificationPreference whereUserId($value)
 * @mixin \Eloquent
 */
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
