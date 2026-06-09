<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Builder;

/** Модель заявки с машиной состояний статусов и связями с командой, организацией и оценками */
class Application extends Model
{
    use SoftDeletes, HasFactory;

    // ---------------------------------------------------------
    // Constants
    // ---------------------------------------------------------

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

    // ---------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------

    protected $fillable = [
        'program_id', 'call_id', 'challenge_id', 'team_id',
        'organization_id', 'status', 'submitted_at', 'approved_at',
        'rejected_at', 'total_score', 'decision_comment',
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

    public function program(): BelongsTo { return $this->belongsTo(Program::class); }
    public function call(): BelongsTo { return $this->belongsTo(Call::class); }
    public function challenge(): BelongsTo { return $this->belongsTo(Challenge::class); }
    public function team(): BelongsTo { return $this->belongsTo(Team::class); }
    public function organization(): BelongsTo { return $this->belongsTo(Organization::class); }

    public function history(): HasMany { return $this->hasMany(ApplicationHistory::class); }
    public function answers(): HasMany { return $this->hasMany(ApplicationAnswer::class); }
    public function pairingSubmissions(): HasMany { return $this->hasMany(ApplicationPairingSubmission::class); }
    public function evaluations(): HasMany { return $this->hasMany(Evaluation::class); }
    public function documents(): HasMany { return $this->hasMany(Document::class); }
    public function project(): HasOne { return $this->hasOne(Project::class); }

    // ---------------------------------------------------------
    // Query Scopes
    // ---------------------------------------------------------

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_DRAFT);
    }
    public function scopeSubmitted(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_SUBMITTED);
    }
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_APPROVED);
    }
    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_REJECTED);
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isProgramA(): bool { return !is_null($this->call_id); }
    public function isProgramB(): bool { return !is_null($this->challenge_id); }

    public function isStatus(string $status): bool { return $this->status === $status; }

    public function isApproved(): bool { return $this->isStatus(self::STATUS_APPROVED); }
    public function isRejected(): bool { return $this->isStatus(self::STATUS_REJECTED); }
    public function isActive(): bool   { return $this->isStatus(self::STATUS_ACTIVE); }

    public function canBeEdited(): bool
    {
        return in_array($this->status, [self::STATUS_DRAFT, self::STATUS_NEEDS_SUPPLEMENT]);
    }

    public function canBeSubmitted(): bool
    {
        return $this->isStatus(self::STATUS_DRAFT);
    }
}
