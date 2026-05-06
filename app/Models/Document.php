<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

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
