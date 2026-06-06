<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $project_id
 * @property string|null $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $deadline
 * @property string|null $status
 * @property int $completion_percentage
 * @property \Illuminate\Support\Carbon|null $completed_at
 * @property int|null $approved_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\User|null $approvedBy
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Consultation> $consultations
 * @property-read int|null $consultations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \App\Models\Project|null $project
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereApprovedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereCompletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereCompletionPercentage($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Milestone withoutTrashed()
 * @mixin \Eloquent
 */
class Milestone extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'deadline',
        'status',
        'completion_percentage',
        'completed_at',
        'approved_by',
    ];

    protected $casts = [
        'deadline'              => 'datetime',
        'completed_at'          => 'datetime',
        'completion_percentage' => 'integer',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'milestone_documents')
            ->withTimestamps();
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isApproved(): bool
    {
        return $this->approved_by !== null;
    }

    public function isOverdue(): bool
    {
        return $this->deadline !== null
            && $this->deadline->isPast()
            && ! $this->isCompleted();
    }
}
