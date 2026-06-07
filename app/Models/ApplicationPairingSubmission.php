<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class ApplicationPairingSubmission extends Model
{
    use SoftDeletes, HasFactory;

    // Typy dokumentov pre Program B párovanie
    const TYPE_CV                  = 'cv';
    const TYPE_MOTIVATION_LETTER   = 'motivation_letter';
    const TYPE_SOLUTION_PROPOSAL   = 'solution_proposal';
    const TYPE_OTHER               = 'other';

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
