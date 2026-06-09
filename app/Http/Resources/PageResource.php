<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Ресурс CMS-страницы с контентом, slug и SEO-полями */
class PageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'      => $this->id,
            'title'   => $this->title,
            'slug'    => $this->slug,
            'content' => $this->content,

            'status' => [
                'is_published' => $this->is_published,
            ],

            'seo' => [
                'meta_title'       => $this->meta_title,
                'meta_description' => $this->meta_description,
            ],
        ];
    }
}
