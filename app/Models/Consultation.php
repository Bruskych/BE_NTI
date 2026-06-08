<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultation extends Model
{
    use SoftDeletes, HasFactory;

    // ---------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------

    protected $fillable = [
        'mentorship_id',
        'mentor_id',
        'milestone_id',
        'scheduled_at',
        'completed_at',
        'notes',
        'recommendations',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function mentorship(): BelongsTo
    {
        return $this->belongsTo(Mentorship::class);
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    // ---------------------------------------------------------
    // Query Scopes
    // ---------------------------------------------------------

    public function scopeCompleted(Builder $query): Builder
    {
        return $query->whereNotNull('completed_at');
    }

    public function scopeUpcoming(Builder $query): Builder
    {
        return $query->whereNull('completed_at')
            ->whereNotNull('scheduled_at')
            ->where('scheduled_at', '>', now());
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isUpcoming(): bool
    {
        return $this->completed_at === null
            && $this->scheduled_at !== null
            && $this->scheduled_at->isFuture();
    }
}
