<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Модель ответа на поле формы заявки */
class ApplicationAnswer extends Model
{
    use SoftDeletes, HasFactory;

    // ---------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------

    protected $fillable = [
        'application_id',
        'field_id',
        'value_text',
        'value_json',
        'file_path',
    ];

    protected $casts = [
        'value_json' => 'array',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function field(): BelongsTo
    {
        return $this->belongsTo(FormField::class, 'field_id');
    }

    // ---------------------------------------------------------
    // Query Scopes
    // ---------------------------------------------------------

    public function scopeForField(Builder $query, int $fieldId): Builder
    {
        return $query->where('field_id', $fieldId);
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function getFormattedValue() // Возвращает значение ответа, независимо от того, где оно хранится
    {
        return $this->value_text ?? $this->value_json ?? $this->file_path;
    }
}
