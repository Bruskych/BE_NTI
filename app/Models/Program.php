<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Program extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'type',
        'description',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'type'      => 'string',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function calls(): HasMany
    {
        return $this->hasMany(Call::class);
    }

    public function challenges(): HasMany
    {
        return $this->hasMany(Challenge::class);
    }

    public function evaluationTemplates(): HasMany
    {
        return $this->hasMany(EvaluationTemplate::class);
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

    public function isGrant(): bool
    {
        return $this->type === 'grant';
    }

    public function isPractice(): bool
    {
        return $this->type === 'practice';
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
