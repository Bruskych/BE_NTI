<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class FormField extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'program_id',
        'call_id',
        'name',
        'label',
        'type',
        'required',
        'options_json',
        'validation_rules',
        'order',
    ];

    protected $casts = [
        'required'     => 'boolean',
        'options_json' => 'array',
        'order'        => 'integer',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function program(): BelongsTo
    {
        return $this->belongsTo(Program::class);
    }

    // null = platné pre celý program; not null = špecifické pre danú výzvu
    public function call(): BelongsTo
    {
        return $this->belongsTo(Call::class);
    }

    public function answers(): HasMany
    {
        return $this->hasMany(ApplicationAnswer::class, 'field_id');
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isProgramWide(): bool
    {
        return $this->call_id === null;
    }
}
