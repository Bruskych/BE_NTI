<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $organization_id
 * @property string|null $logo_path
 * @property string|null $website_link
 * @property bool $is_featured
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Organization|null $organization
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereIsFeatured($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereLogoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereOrganizationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner whereWebsiteLink($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Partner withoutTrashed()
 * @mixin \Eloquent
 */
class Partner extends Model
{
    use SoftDeletes;

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
