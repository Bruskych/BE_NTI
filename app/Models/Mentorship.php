<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $project_id
 * @property int|null $mentor_id
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $started_at
 * @property \Illuminate\Support\Carbon|null $finished_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Consultation> $consultations
 * @property-read int|null $consultations_count
 * @property-read \App\Models\User|null $mentor
 * @property-read \App\Models\Project|null $project
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship whereFinishedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship whereMentorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship whereStartedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Mentorship withoutTrashed()
 * @mixin \Eloquent
 */
class Mentorship extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'mentor_id',
        'status',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class)->orderBy('scheduled_at');
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isFinished(): bool
    {
        return $this->status === 'finished';
    }
}
