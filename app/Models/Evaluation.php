<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evaluation extends Model
{
    use SoftDeletes, HasFactory;

    // ---------------------------------------------------------
    // Constants
    // ---------------------------------------------------------

    const RECOMMENDATION_APPROVE = 'approve';
    const RECOMMENDATION_REJECT  = 'reject';

    // ---------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------

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
    // Query Scopes
    // ---------------------------------------------------------

    public function scopeRecommended(Builder $query): Builder
    {
        return $query->where('recommendation', self::RECOMMENDATION_APPROVE);
    }

    public function scopeRejected(Builder $query): Builder
    {
        return $query->where('recommendation', self::RECOMMENDATION_REJECT);
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isRecommended(): bool
    {
        return $this->recommendation === self::RECOMMENDATION_APPROVE;
    }

    public function isRejected(): bool
    {
        return $this->recommendation === self::RECOMMENDATION_REJECT;
    }
}
