<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationScore extends Model
{
    use SoftDeletes, HasFactory;

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
