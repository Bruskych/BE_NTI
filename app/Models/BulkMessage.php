<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $sender_id
 * @property string|null $target_group
 * @property string|null $subject
 * @property string|null $body
 * @property \Illuminate\Support\Carbon|null $sent_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $recipients
 * @property-read int|null $recipients_count
 * @property-read \App\Models\User|null $sender
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage whereBody($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage whereSenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage whereSentAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage whereSubject($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage whereTargetGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BulkMessage withoutTrashed()
 * @mixin \Eloquent
 */
class BulkMessage extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'sender_id',
        'target_group',
        'subject',
        'body',
        'sent_at',
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function sender(): BelongsTo
    {
        return $this->belongsTo(User::class, 'sender_id');
    }

    public function recipients(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'bulk_message_recipients')
            ->withPivot('delivered_at');
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isSent(): bool
    {
        return $this->sent_at !== null;
    }

    public function recipientCount(): int
    {
        return $this->recipients()->count();
    }
}
