<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Модель балла по отдельному критерию в рамках оценки заявки */
class EvaluationScore extends Model
{
    use SoftDeletes, HasFactory;

    // ---------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------

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

    // ---------------------------------------------------------
    // Query Scopes
    // ---------------------------------------------------------

    public function scopeForEvaluation(Builder $query, int $evaluationId): Builder
    {
        return $query->where('evaluation_id', $evaluationId);
    }

    public function scopeForCriteria(Builder $query, int $criteriaId): Builder
    {
        return $query->where('criteria_id', $criteriaId);
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isPassingScore(float $threshold = 5.0): bool // Выше ли оценка определенного порога
    {
        return $this->score >= $threshold;
    }
}
