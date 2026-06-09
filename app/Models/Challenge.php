<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/** Модель задачи от компании (Programme B) с машиной состояний и ограничением по заявкам */
class Challenge extends Model
{
    use SoftDeletes, HasFactory;

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
        // АДМИН - ВИДИТ ВСЁ
        if ($user->can('challenges.view-all')) {
            return $query;
        }
        // ГОСТЬ И СТУДЕНТ - ВИДЯТ ТОЛЬКО ОПУБЛИКОВАНЫЕ И АКТИВНЫЕ
        if ($user->hasAnyRole(['student', 'visitor'])) {
            return $query->whereIn('status', [
                self::STATUS_PUBLISHED,
                self::STATUS_PAIRING,
                self::STATUS_ASSIGNED,
                self::STATUS_ACTIVE
            ]);
        }
        // ОРГАНИЗАЦИИ - ВИДЯТ ПУБЛИЧНЫЕ И СВОИ ЧЕРНОВИКИ
        return $query->where(function ($q) use ($user) {
            $q->whereIn('status', [self::STATUS_PUBLISHED, self::STATUS_PAIRING, self::STATUS_ASSIGNED, self::STATUS_ACTIVE])
                ->orWhere(function ($sub) use ($user) {
                    $sub->where('status', self::STATUS_DRAFT)
                        ->whereIn('organization_id', $user->organizations()->select('organizations.id'));
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
