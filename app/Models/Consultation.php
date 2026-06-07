<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Consultation extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'mentorship_id',
        'mentor_id',
        'milestone_id',
        'scheduled_at',
        'completed_at',
        'notes',
        'recommendations',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function mentorship(): BelongsTo
    {
        return $this->belongsTo(Mentorship::class);
    }

    public function mentor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'mentor_id');
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    public function isUpcoming(): bool
    {
        return $this->completed_at === null
            && $this->scheduled_at !== null
            && $this->scheduled_at->isFuture();
    }
}
