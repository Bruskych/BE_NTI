<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $evaluation_id
 * @property int $criteria_id
 * @property numeric|null $score
 * @property string|null $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\EvaluationCriteria|null $criteria
 * @property-read \App\Models\Evaluation|null $evaluation
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore whereCriteriaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore whereEvaluationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationScore whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class EvaluationScore extends Model
{
    protected $fillable = [
        'evaluation_id',
        'criteria_id',
        'score',
        'comment',
    ];

    protected $casts = [
        'score' => 'decimal:2',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function evaluation(): BelongsTo
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function criteria(): BelongsTo
    {
        return $this->belongsTo(EvaluationCriteria::class, 'criteria_id');
    }
}
