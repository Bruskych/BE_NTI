<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $template_id
 * @property string|null $name
 * @property string|null $description
 * @property numeric|null $weight
 * @property int|null $order
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EvaluationScore> $scores
 * @property-read int|null $scores_count
 * @property-read \App\Models\EvaluationTemplate|null $template
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria whereOrder($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria whereTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria whereWeight($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationCriteria withoutTrashed()
 * @mixin \Eloquent
 */
class EvaluationCriteria extends Model
{
    use SoftDeletes;

    protected $table = 'evaluation_criteria';

    protected $fillable = [
        'template_id',
        'name',
        'description',
        'weight',
        'order',
    ];

    protected $casts = [
        'weight' => 'decimal:2',
        'order'  => 'integer',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function template(): BelongsTo
    {
        return $this->belongsTo(EvaluationTemplate::class, 'template_id');
    }

    public function scores(): HasMany
    {
        return $this->hasMany(EvaluationScore::class, 'criteria_id');
    }
}
