<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $program_id
 * @property string|null $name
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Call> $calls
 * @property-read int|null $calls_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\EvaluationCriteria> $criteria
 * @property-read int|null $criteria_count
 * @property-read \App\Models\Program|null $program
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|EvaluationTemplate withoutTrashed()
 * @mixin \Eloquent
 */
class EvaluationTemplate extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'program_id',
        'name',
        'description',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function criteria(): HasMany
    {
        return $this->hasMany(EvaluationCriteria::class, 'template_id')
            ->orderBy('order');
    }

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class, 'evaluation_template_id');
    }
}
