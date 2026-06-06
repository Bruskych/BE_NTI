<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'status'           => 'string',
        'max_applications' => 'integer',
        'backlog_order'    => 'integer',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

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

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isPublished(): bool
    {
        return $this->status === 'published';
    }

    public function isPairing(): bool
    {
        return $this->status === 'pairing';
    }

    public function isAssigned(): bool
    {
        return $this->status === 'assigned';
    }

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function hasCapacity(): bool
    {
        return $this->applications()->count() < $this->max_applications;
    }
}
