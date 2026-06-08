<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mentorship extends Model
{
    use SoftDeletes, HasFactory;

    // ---------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------

    protected $fillable = [
        'project_id',
        'mentor_id',
        'status',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at'  => 'datetime',
        'finished_at' => 'datetime',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class)->orderBy('scheduled_at');
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
}
