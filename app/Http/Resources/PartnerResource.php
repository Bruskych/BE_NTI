<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

/** Ресурс партнёра с логотипом и ссылкой */
class PartnerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'logo_url'     => $this->logo_path ? Storage::disk('public')->url($this->logo_path) : null,
            'website_link' => $this->website_link,
            'is_featured'  => $this->is_featured,
            'organization' => $this->whenLoaded('organization', fn() =>
            new OrganizationResource($this->organization)
            ),
        ];
    }
}
