<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Ресурс организации с логотипом, сектором и статусом */
class OrganizationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'tax_id'       => $this->tax_id,
            'sector'       => $this->sector,
            'website_link' => $this->website_link,
            'description'  => $this->description,
            'status'       => $this->status,
            'is_active'    => $this->isActive(),
            'logo_url'     => $this->getFirstMediaUrl('logo') ?: null,
            'users'        => $this->whenLoaded('users', fn() =>
            UserResource::collection($this->users)
            ),
            'created_at'   => $this->created_at,
        ];
    }
}
