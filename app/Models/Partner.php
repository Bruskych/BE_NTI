<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use SoftDeletes, HasFactory;

    // ---------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------

    protected $fillable = [
        'organization_id',
        'logo_path',
        'website_link',
        'is_featured',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    // ---------------------------------------------------------
    // Query Scopes
    // ---------------------------------------------------------

    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    public function scopeNotFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', false);
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isFeatured(): bool
    {
        return $this->is_featured;
    }
}
