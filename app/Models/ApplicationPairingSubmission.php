<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Модель документа парного отбора (CV, мотивационное письмо, предложение решения) для программы B */
class ApplicationPairingSubmission extends Model
{
    use SoftDeletes, HasFactory;

    // ---------------------------------------------------------
    // Constants
    // ---------------------------------------------------------

    const TYPE_CV                  = 'cv';
    const TYPE_MOTIVATION_LETTER   = 'motivation_letter';
    const TYPE_SOLUTION_PROPOSAL   = 'solution_proposal';
    const TYPE_OTHER               = 'other';

    // ---------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------

    protected $fillable = [
        'application_id',
        'type',
        'file_path',
        'notes',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    // ---------------------------------------------------------
    // Query Scopes
    // ---------------------------------------------------------

    public function scopeCv(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_CV);
    }

    public function scopeMotivationLetter(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_MOTIVATION_LETTER);
    }

    public function scopeSolutionProposal(Builder $query): Builder
    {
        return $query->where('type', self::TYPE_SOLUTION_PROPOSAL);
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isCv(): bool
    {
        return $this->type === self::TYPE_CV;
    }

    public function isMotivationLetter(): bool
    {
        return $this->type === self::TYPE_MOTIVATION_LETTER;
    }

    public function isSolutionProposal(): bool
    {
        return $this->type === self::TYPE_SOLUTION_PROPOSAL;
    }
}
