<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $mentorship_id
 * @property int|null $mentor_id
 * @property int|null $milestone_id
 * @property \Illuminate\Support\Carbon|null $scheduled_at
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property string|null $notes
 * @property string|null $recommendations
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $mentor
 * @property-read \App\Models\Mentorship|null $mentorship
 * @property-read \App\Models\Milestone|null $milestone
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereMentorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereMentorshipId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereMilestoneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereRecommendations($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereScheduledAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Consultation withoutTrashed()
 * @mixin \Eloquent
 */
class Consultation extends Model
{
    use SoftDeletes;

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
