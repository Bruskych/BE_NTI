<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Organization extends Model implements HasMedia
{
    use SoftDeletes, HasFactory, InteractsWithMedia;

    // ---------------------------------------------------------
    // Constants
    // ---------------------------------------------------------

    const STATUS_ACTIVE = 'active';

    // ---------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------

    protected $fillable = [
        'name',
        'tax_id',
        'sector',
        'website_link',
        'description',
        'status',
    ];

    protected $casts = [
        'status' => 'string',
    ];

    // ---------------------------------------------------------
    // Media Library
    // ---------------------------------------------------------

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('logo')
            ->singleFile()
            ->acceptsMimeTypes(['image/jpeg', 'image/png', 'image/jpg', 'image/webp']);
    }

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'organization_user')
            ->withPivot('role')
            ->withTimestamps();
    }

    public function challenges(): HasMany
    {
        return $this->hasMany(Challenge::class);
    }

    public function applications(): HasMany
    {
        return $this->hasMany(Application::class);
    }

    public function partner(): HasMany
    {
        return $this->hasMany(Partner::class);
    }

    public function owners(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'owner');
    }

    public function productOwners(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'product_owner');
    }

    // ---------------------------------------------------------
    // Query Scopes
    // ---------------------------------------------------------

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }
}
