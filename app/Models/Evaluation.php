<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $application_id
 * @property int|null $evaluator_id
 * @property numeric|null $total_score
 * @property string|null $comment
 * @property string|null $recommendation
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Application|null $application
 * @property-read \App\Models\User|null $evaluator
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EvaluationScore> $scores
 * @property-read int|null $scores_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereEvaluatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereRecommendation($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereTotalScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Evaluation withoutTrashed()
 * @mixin \Eloquent
 */
class Evaluation extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'application_id',
        'evaluator_id',
        'total_score',
        'comment',
        'recommendation',
    ];

    protected $casts = [
        'total_score' => 'decimal:2',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function evaluator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'evaluator_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class);
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isRecommended(): bool
    {
        return $this->recommendation === 'approve';
    }

    public function isRejected(): bool
    {
        return $this->recommendation === 'reject';
    }
}
