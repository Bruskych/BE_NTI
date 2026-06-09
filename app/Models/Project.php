<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Модель проекта, созданного по одобренной заявке, с контрольными точками и менторством */
class Project extends Model
{
    use SoftDeletes, HasFactory;

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
    // Helpers
    // ---------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function isFinished(): bool
    {
        return $this->status === 'finished';
    }

    public function completionPercentage(): int
    {
        $milestones = $this->milestones;

        if ($milestones->isEmpty()) {
            return 0;
        }

        return (int) $milestones->avg('completion_percentage');
    }

    public function getTeamAttribute()
    {
        return $this->application->team;
    }
}
