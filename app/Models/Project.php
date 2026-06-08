<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes, HasFactory;

    // ---------------------------------------------------------
    // Constants
    // ---------------------------------------------------------

    const STATUS_ACTIVE   = 'active';
    const STATUS_FINISHED = 'finished';

    // ---------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------

    protected $fillable = [
        'application_id',
        'title',
        'description',
        'status',
        'started_at',
        'finished_at',
        'final_score',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
        'final_score' => 'decimal:2',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function mentorship(): HasOne
    {
        return $this->hasOne(Mentorship::class);
    }

    public function milestones(): HasMany
    {
        return $this->hasMany(Milestone::class)->orderBy('deadline');
    }

    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    // ---------------------------------------------------------
    // Query Scopes
    // ---------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopeFinished(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_FINISHED);
    }

    // ---------------------------------------------------------
    // Accessors & Helpers
    // ---------------------------------------------------------

    public function getTeamAttribute()
    {
        return $this->application->team;
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    public function isFinished(): bool
    {
        return $this->status === self::STATUS_FINISHED;
    }

    public function completionPercentage(): int
    {
        $milestones = $this->milestones;

        if ($milestones->isEmpty()) {
            return 0;
        }

        return (int) $milestones->avg('completion_percentage');
    }
}
