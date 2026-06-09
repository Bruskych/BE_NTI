<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Ресурс консультации с ментором, временем и контрольной точкой */
class ConsultationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'scheduled_at'    => $this->scheduled_at,
            'completed_at'    => $this->completed_at,
            'is_completed'    => $this->isCompleted(),
            'is_upcoming'     => $this->isUpcoming(),
            'notes'           => $this->notes,
            'recommendations' => $this->recommendations,
            'mentor'          => $this->whenLoaded('mentor', fn() =>
            new UserResource($this->mentor)
            ),
            'milestone'       => $this->whenLoaded('milestone', fn() =>
            new MilestoneResource($this->milestone)
            ),
        ];
    }
}
