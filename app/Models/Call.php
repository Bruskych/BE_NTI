<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Call extends Model
{
    use SoftDeletes, HasFactory;

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
