<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int|null $user_id
 * @property string|null $action
 * @property string|null $object_type
 * @property int|null $object_id
 * @property array<array-key, mixed>|null $old_values_json
 * @property array<array-key, mixed>|null $new_values_json
 * @property string|null $ip_address
 * @property string|null $user_agent
 * @property string|null $result
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereAction($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereIpAddress($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereNewValuesJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereObjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereObjectType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereOldValuesJson($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereResult($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereUserAgent($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|AuditEvent whereUserId($value)
 * @mixin \Eloquent
 */
class AuditEvent extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_id',
        'action',
        'object_type',
        'object_id',
        'old_values_json',
        'new_values_json',
        'ip_address',
        'user_agent',
        'result',
        'created_at',
    ];

    protected $casts = [
        'old_values_json' => 'array',
        'new_values_json' => 'array',
        'created_at'      => 'datetime',
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

    public function isSuccessful(): bool
    {
        return $this->result === 'success';
    }
}
