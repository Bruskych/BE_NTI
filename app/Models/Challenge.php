<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
