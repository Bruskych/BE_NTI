<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Модель контрольной точки проекта с процентом выполнения и подтверждением */
class Milestone extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'project_id',
        'title',
        'description',
        'deadline',
        'status',
        'completion_percentage',
        'completed_at',
        'approved_by',
    ];

    protected $casts = [
        'deadline'              => 'datetime',
        'completed_at'          => 'datetime',
        'completion_percentage' => 'integer',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function documents(): BelongsToMany
    {
        return $this->belongsToMany(Document::class, 'milestone_documents')
            ->withTimestamps();
    }

    public function consultations(): HasMany
    {
        return $this->hasMany(Consultation::class);
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isApproved(): bool
    {
        return $this->approved_by !== null;
    }

    public function isOverdue(): bool
    {
        return $this->deadline !== null
            && $this->deadline->isPast()
            && ! $this->isCompleted();
    }

    public function markAsApproved(int $userId): void
    {
        $this->update([
            'approved_by'           => $userId,
            'status'                => 'completed',
            'completed_at'          => now(),
            'completion_percentage' => 100,
        ]);
    }
}
