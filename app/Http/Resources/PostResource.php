<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Ресурс публикации блога с SEO-полями, автором и статусом публикации */
class PostResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'title'          => $this->title,
            'slug'           => $this->slug,
            'excerpt'        => $this->excerpt,
            'content'        => $this->content,
            'featured_image' => $this->featured_image,

            'status' => [
                'is_published' => $this->is_published,
                'published_at' => $this->published_at?->toIso8601String(),
            ],

            'seo' => [
                'meta_title'       => $this->meta_title,
                'meta_description' => $this->meta_description,
                'og_image'         => $this->og_image,
            ],

            'author' => $this->whenLoaded('author', fn() => new UserResource($this->author)),
        ];
    }
}
