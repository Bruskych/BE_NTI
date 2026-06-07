<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/**
 * @property int $id
 * @property int $program_id
 * @property int $organization_id
 * @property string|null $title
 * @property string|null $description
 * @property string|null $technical_specification
 * @property numeric|null $budget
 * @property int|null $product_owner_id
 * @property \Illuminate\Support\Carbon|null $deadline
 * @property string $status
 * @property int $max_applications
 * @property int $backlog_order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Application> $applications
 * @property-read int|null $applications_count
 * @property-read \App\Models\Organization|null $organization
 * @property-read \App\Models\User|null $productOwner
 * @property-read \App\Models\Program|null $program
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Specialization> $specializations
 * @property-read int|null $specializations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereBacklogOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereMaxApplications($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereProductOwnerId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereTechnicalSpecification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Challenge withoutTrashed()
 * @mixin \Eloquent
 */
class Challenge extends Model
{
    use SoftDeletes;

    const STATUS_DRAFT     = 'draft';
    const STATUS_PUBLISHED = 'published';
    const STATUS_PAIRING   = 'pairing';
    const STATUS_ASSIGNED  = 'assigned';
    const STATUS_ACTIVE    = 'active';
    const STATUS_CLOSED    = 'closed';

    protected $fillable = [
        'program_id',
        'organization_id',
        'title',
        'description',
        'technical_specification',
        'budget',
        'product_owner_id',
        'deadline',
        'status',
        'max_applications',
        'backlog_order',
    ];

    protected $casts = [
        'deadline'         => 'datetime',
        'budget'           => 'decimal:2',
        'max_applications' => 'integer',
        'backlog_order'    => 'integer',
    ];

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    public function productOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'product_owner_id');
    }

    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specialization::class, 'challenge_specialization');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }



    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->can('challenges.view-all')) {
            return $query;
        }
        if ($user->hasRole(['student', 'visitor'])) {
            return $query->whereIn('status', [self::STATUS_PUBLISHED, self::STATUS_PAIRING, self::STATUS_ASSIGNED, self::STATUS_ACTIVE]);
        }

        $myOrgIds = $user->organizations()->pluck('organizations.id');

        return $query->where(function ($q) use ($myOrgIds) {
            $q->whereIn('status', [self::STATUS_PUBLISHED, self::STATUS_PAIRING, self::STATUS_ASSIGNED, self::STATUS_ACTIVE])
                ->orWhere(function ($sub) use ($myOrgIds) {
                    $sub->where('status', self::STATUS_DRAFT)
                        ->whereIn('organization_id', $myOrgIds);
                });
        });
    }

    public function isDraft(): bool     { return $this->status === self::STATUS_DRAFT; }
    public function isPublished(): bool { return $this->status === self::STATUS_PUBLISHED; }
    public function isPairing(): bool   { return $this->status === self::STATUS_PAIRING; }
    public function isAssigned(): bool  { return $this->status === self::STATUS_ASSIGNED; }
    public function isActive(): bool    { return $this->status === self::STATUS_ACTIVE; }
    public function isClosed(): bool    { return $this->status === self::STATUS_CLOSED; }

    public function hasCapacity(): bool
    {
        return $this->applications()->count() < $this->max_applications;
    }
}
