<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                => $this->id,
            'name'              => $this->name,
            'email'             => $this->email,
            'avatar_path'       => $this->avatar_path,
            'avatar_url'        => $this->avatar_path ? asset('storage/' . $this->avatar_path) : null,
            'email_verified_at' => $this->email_verified_at,
            'roles'             => $this->getRoleNames(),
            'permissions'       => $this->whenLoaded('permissions', fn() =>
            $this->getAllPermissions()->pluck('name')
            ),
            'student_profile'   => $this->whenLoaded('studentProfile', fn() =>
            new StudentProfileResource($this->studentProfile)
            ),
            'organizations'     => $this->whenLoaded('organizations', fn() =>
            OrganizationResource::collection($this->organizations)
            ),
            'created_at'        => $this->created_at,
        ];
    }
}
