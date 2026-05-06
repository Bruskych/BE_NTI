<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EvaluationCriteria extends Model
{
    use SoftDeletes;

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
