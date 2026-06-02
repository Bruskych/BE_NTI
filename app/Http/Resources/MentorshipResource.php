<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MentorshipResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'            => $this->id,
            'status'        => $this->status,
            'is_active'     => $this->isActive(),
            'started_at'    => $this->started_at,
            'finished_at'   => $this->finished_at,
            'mentor'        => $this->whenLoaded('mentor', fn() =>
            new UserResource($this->mentor)
            ),
            'consultations' => $this->whenLoaded('consultations', fn() =>
            ConsultationResource::collection($this->consultations)
            ),
        ];
    }
}
