<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $program_id
 * @property int|null $call_id
 * @property int|null $challenge_id
 * @property int $team_id
 * @property int|null $organization_id
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $submitted_at
 * @property \Illuminate\Support\Carbon|null $approved_at
 * @property \Illuminate\Support\Carbon|null $rejected_at
 * @property numeric|null $total_score
 * @property string|null $decision_comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApplicationAnswer> $answers
 * @property-read int|null $answers_count
 * @property-read \App\Models\Call|null $call
 * @property-read \App\Models\Challenge|null $challenge
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Document> $documents
 * @property-read int|null $documents_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Evaluation> $evaluations
 * @property-read int|null $evaluations_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApplicationHistory> $history
 * @property-read int|null $history_count
 * @property-read \App\Models\Organization|null $organization
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApplicationPairingSubmission> $pairingSubmissions
 * @property-read int|null $pairing_submissions_count
 * @property-read \App\Models\Program|null $program
 * @property-read \App\Models\Project|null $project
 * @property-read \App\Models\Team|null $team
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereApprovedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereCallId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereChallengeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereDecisionComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereRejectedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereSubmittedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereTeamId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereTotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Application withoutTrashed()
 * @mixin \Eloquent
 */
class Application extends Model
{
    use SoftDeletes;

    // Stavový automat:
    // draft → submitted → verified → in_evaluation → needs_supplement
    // → approved / rejected → onboarding → active → suspended → archived
    const STATUS_DRAFT            = 'draft';
    const STATUS_SUBMITTED        = 'submitted';
    const STATUS_VERIFIED         = 'verified';
    const STATUS_IN_EVALUATION    = 'in_evaluation';
    const STATUS_NEEDS_SUPPLEMENT = 'needs_supplement';
    const STATUS_APPROVED         = 'approved';
    const STATUS_REJECTED         = 'rejected';
    const STATUS_ONBOARDING       = 'onboarding';
    const STATUS_ACTIVE           = 'active';
    const STATUS_SUSPENDED        = 'suspended';
    const STATUS_ARCHIVED         = 'archived';

    protected $fillable = [
        'program_id',
        'call_id',
        'challenge_id',
        'team_id',
        'organization_id',
        'status',
        'submitted_at',
        'approved_at',
        'rejected_at',
        'total_score',
        'decision_comment',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'approved_at'  => 'datetime',
        'rejected_at'  => 'datetime',
        'total_score'  => 'decimal:2',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }

    public function challenge(): BelongsTo
    {
        return $this->belongsTo(Challenge::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(ApplicationHistory::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ApplicationAnswer::class);
    }

    public function pairingSubmissions(): HasMany
    {
        return $this->hasMany(ApplicationPairingSubmission::class);
    }

    public function evaluations(): HasMany
    {
        return $this->hasMany(Evaluation::class);
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isProgramA(): bool
    {
        return $this->call_id !== null;
    }

    public function isProgramB(): bool
    {
        return $this->challenge_id !== null;
    }

    public function isApproved(): bool
    {
        return $this->status === self::STATUS_APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [
            self::STATUS_DRAFT,
            self::STATUS_NEEDS_SUPPLEMENT,
        ]);
    }

    public function canBeSubmitted(): bool
    {
        return $this->status === self::STATUS_DRAFT;
    }

    public function scopeDraft($query)
    {
        return $query->where('status', self::STATUS_DRAFT);
    }

    public function scopeSubmitted($query)
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }
}
