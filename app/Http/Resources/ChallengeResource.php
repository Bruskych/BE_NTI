<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Ресурс задачи компании со статусом, специализациями и организацией */
class ChallengeResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                      => $this->id,
            'title'                   => $this->title,
            'description'             => $this->description,
            'technical_specification' => $this->technical_specification,
            'budget'                  => $this->budget,
            'deadline'                => $this->deadline,
            'status'                  => $this->status,
            'max_applications'        => $this->max_applications,
            'has_capacity'            => $this->hasCapacity(),
            'backlog_order'           => $this->backlog_order,
            'program'                 => $this->whenLoaded('program', fn() =>
            new ProgramResource($this->program)
            ),
            'organization'            => $this->whenLoaded('organization', fn() =>
            new OrganizationResource($this->organization)
            ),
            'product_owner'           => $this->whenLoaded('productOwner', fn() =>
            new UserResource($this->productOwner)
            ),
            'specializations'         => $this->whenLoaded('specializations', fn() =>
            SpecializationResource::collection($this->specializations)
            ),
            'applications_count'      => $this->whenCounted('applications'),
            'created_at'              => $this->created_at,
        ];
    }
}
