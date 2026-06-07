<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Report extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'name',
        'type',
        'parameters_json',
        'generated_by',
        'file_path',
    ];

    protected $casts = [
        'parameters_json' => 'array',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function generatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isGenerated(): bool
    {
        return $this->file_path !== null;
    }
}
