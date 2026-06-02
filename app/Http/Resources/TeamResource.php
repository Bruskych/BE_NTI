<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'name'             => $this->name,
            'description'      => $this->description,
            'skills'           => $this->skills_json,
            'capacity'         => $this->capacity,
            'status'           => $this->status,
            'is_full'          => $this->isFull(),
            'leader'           => $this->whenLoaded('leader', fn() =>
            new UserResource($this->leader)
            ),
            'members'          => $this->whenLoaded('members', fn() =>
            $this->members->map(fn($member) => [
                'id'        => $member->id,
                'name'      => $member->name,
                'email'     => $member->email,
                'team_role' => $member->pivot->role,
                'joined_at' => $member->pivot->joined_at,
            ])
            ),
            'specializations'  => $this->whenLoaded('specializations', fn() =>
            SpecializationResource::collection($this->specializations)
            ),
            'members_count'    => $this->whenCounted('members'),
            'created_at'       => $this->created_at,
        ];
    }
}
