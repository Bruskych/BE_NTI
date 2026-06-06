<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $program_id
 * @property string|null $title
 * @property string|null $description
 * @property \Illuminate\Support\Carbon|null $deadline
 * @property string $status
 * @property numeric|null $budget
 * @property int|null $evaluation_template_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Application> $applications
 * @property-read int|null $applications_count
 * @property-read \App\Models\EvaluationTemplate|null $evaluationTemplate
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FormField> $formFields
 * @property-read int|null $form_fields_count
 * @property-read \App\Models\Program|null $program
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Specialization> $specializations
 * @property-read int|null $specializations_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereBudget($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereDeadline($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereEvaluationTemplateId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Call withoutTrashed()
 * @mixin \Eloquent
 */
class Call extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'program_id',
        'title',
        'description',
        'deadline',
        'status',
        'budget',
        'evaluation_template_id',
    ];

    protected $casts = [
        'deadline' => 'datetime',
        'budget'   => 'decimal:2',
        'status'   => 'string',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    public function evaluationTemplate(): BelongsTo
    {
        return $this->belongsTo(EvaluationTemplate::class);
    }

    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specialization::class, 'call_specialization');
    }

    public function formFields(): HasMany
    {
        return $this->hasMany(FormField::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isOpen(): bool
    {
        return $this->status === 'open';
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isExpired(): bool
    {
        return $this->deadline !== null && $this->deadline->isPast();
    }
}
