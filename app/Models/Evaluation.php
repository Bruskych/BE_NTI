<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Evaluation extends Model
{
    use SoftDeletes, HasFactory;

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
