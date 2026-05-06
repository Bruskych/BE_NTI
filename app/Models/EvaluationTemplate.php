<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
