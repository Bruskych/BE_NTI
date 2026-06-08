<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Team extends Model
{
    use SoftDeletes, HasFactory;

    // ---------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------

    protected $fillable = [
        'name',
        'leader_id',
        'description',
        'skills_json',
        'capacity',
        'status',
    ];

    protected $casts = [
        'skills_json' => 'array',
        'capacity'    => 'integer',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_user')
            ->withPivot('role', 'joined_at')
            ->withTimestamps();
    }

    public function specializations(): BelongsToMany
    {
        return $this->belongsToMany(Specialization::class, 'team_specialization');
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isFull(): bool
    {
        return $this->capacity !== null && $this->members()->count() >= $this->capacity;
    }

    public function hasMember(int $userId): bool
    {
        return $this->members()->where('user_id', $userId)->exists();
    }
}
