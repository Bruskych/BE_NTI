<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;

class Organization extends Model implements HasMedia
{
    use SoftDeletes, InteractsWithMedia;

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

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    public function owners(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'owner');
    }

    public function productOwners(): BelongsToMany
    {
        return $this->users()->wherePivot('role', 'product_owner');
    }
}
