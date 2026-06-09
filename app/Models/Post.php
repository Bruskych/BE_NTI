<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Модель публикации блога с SEO-полями, slug и управлением статусом публикации */
class Post extends Model
{
    use SoftDeletes, HasFactory;

    // ---------------------------------------------------------
    // Configuration
    // ---------------------------------------------------------

    const TYPE_ARTICLE       = 'article';
    const TYPE_FAQ           = 'faq';
    const TYPE_SUCCESS_STORY = 'success_story';

    protected $fillable = [
        'author_id',
        'type',
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'meta_title',
        'meta_description',
        'og_image',
        'is_published',
        'published_at',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    // ---------------------------------------------------------
    // Relationships
    // ---------------------------------------------------------

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    // ---------------------------------------------------------
    // Query Scopes
    // ---------------------------------------------------------

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeDraft(Builder $query): Builder
    {
        return $query->where('is_published', false)
            ->orWhere('published_at', '>', now());
    }

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isPublished(): bool
    {
        return $this->is_published && $this->published_at?->isPast();
    }

    public function publish(): void
    {
        $this->update([
            'is_published' => true,
            'published_at' => $this->published_at ?? now(),
        ]);
    }
}
