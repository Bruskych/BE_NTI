<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Partner extends Model
{
    use SoftDeletes, HasFactory;

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
    // Helpers
    // ---------------------------------------------------------

    public function isFeatured(): bool
    {
        return $this->is_featured;
    }
}
