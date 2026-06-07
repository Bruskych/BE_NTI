<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int|null $application_id
 * @property int|null $project_id
 * @property int|null $milestone_id
 * @property string|null $type
 * @property string|null $file_name
 * @property string|null $file_path
 * @property string|null $mime_type
 * @property int|null $size
 * @property int $version
 * @property string $classification
 * @property int|null $uploaded_by
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Application|null $application
 * @property-read \App\Models\Milestone|null $milestone
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Milestone> $milestones
 * @property-read int|null $milestones_count
 * @property-read \App\Models\Project|null $project
 * @property-read \App\Models\User|null $uploadedBy
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereClassification($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereFileName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereMilestoneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereMimeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereProjectId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereSize($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereType($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereUploadedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document whereVersion($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document withTrashed(bool $withTrashed = true)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Document withoutTrashed()
 * @mixin \Eloquent
 */
class Document extends Model
{
    use SoftDeletes;

    const CLASSIFICATION_PUBLIC       = 'public';
    const CLASSIFICATION_INTERNAL     = 'internal';
    const CLASSIFICATION_CONFIDENTIAL = 'confidential';

    protected $fillable = [
        'application_id',
        'project_id',
        'milestone_id',
        'type',
        'file_name',
        'file_path',
        'mime_type',
        'size',
        'version',
        'classification',
        'uploaded_by',
    ];

    protected $casts = [
        'size'    => 'integer',
        'version' => 'integer',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function milestone(): BelongsTo
    {
        return $this->belongsTo(Milestone::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function milestones(): BelongsToMany
    {
        return $this->belongsToMany(Milestone::class, 'milestone_documents')
            ->withTimestamps();
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isPublic(): bool
    {
        return $this->classification === self::CLASSIFICATION_PUBLIC;
    }

    public function isConfidential(): bool
    {
        return $this->classification === self::CLASSIFICATION_CONFIDENTIAL;
    }

    public function fileSizeInKb(): float
    {
        return round($this->size / 1024, 2);
    }
}
