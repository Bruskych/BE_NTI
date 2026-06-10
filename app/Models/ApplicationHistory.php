<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Модель записи истории изменений статуса заявки */
class ApplicationHistory extends Model
{
    use HasFactory;

    // ---------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------

    protected $table = 'application_history';
    public $timestamps = false;

    protected $fillable = [
        'application_id',
        'old_status',
        'new_status',
        'changed_by',
        'comment',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function changedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }

    // ---------------------------------------------------------
    // Query Scopes
    // ---------------------------------------------------------

    public function scopeForApplication(Builder $query, int $applicationId): Builder // Позволяет быстро получить историю конкретной заявки
    {
        return $query->where('application_id', $applicationId)
            ->latest('created_at');
    }
}
