<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Модель критерия оценки с весовым коэффициентом и порядком отображения */
class EvaluationCriteria extends Model
{
    use SoftDeletes, HasFactory;

    // ---------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------

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

    // ---------------------------------------------------------
    // Query Scopes
    // ---------------------------------------------------------

    public function scopeForTemplate(Builder $query, int $templateId): Builder
    {
        return $query->where('template_id', $templateId);
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('order', 'asc');
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isHighPriority(): bool
    {
        return $this->weight > 0.5;
    }
}
