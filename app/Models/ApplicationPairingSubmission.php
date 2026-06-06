<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $application_id
 * @property string $type
 * @property string|null $file_path
 * @property string|null $notes
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Application|null $application
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission whereNotes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ApplicationPairingSubmission whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class ApplicationPairingSubmission extends Model
{
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
