<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Модель CMS-страницы с slug, контентом и статусом публикации */
class Page extends Model
{
    use SoftDeletes, HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'content',
        'meta_title',
        'meta_description',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    // ---------------------------------------------------------
    // Helpers
    // ---------------------------------------------------------

    public function isPublished(): bool
    {
        return $this->is_published;
    }
}
