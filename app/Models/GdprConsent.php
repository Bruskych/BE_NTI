<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $user_id
 * @property string|null $consent_type
 * @property string|null $version
 * @property \Illuminate\Support\Carbon $accepted_at
 * @property string|null $ip_address
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent whereAcceptedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent whereConsentType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GdprConsent whereVersion($value)
 * @mixin \Eloquent
 */
class GdprConsent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'consent_type',
        'version',
        'accepted_at',
        'ip_address',
        'created_at',
    ];

    protected $casts = [
        'accepted_at' => 'datetime',
        'created_at'  => 'datetime',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
