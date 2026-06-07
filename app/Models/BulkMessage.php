<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class BulkMessage extends Model
{
    use SoftDeletes, HasFactory;

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
