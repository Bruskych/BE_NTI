<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Ресурс проекта с командой, контрольными точками и менторством */
class ProjectResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'title'                => $this->title,
            'description'          => $this->description,
            'status'               => $this->status,
            'completion_percentage'=> $this->completionPercentage(),
            'started_at'           => $this->started_at,
            'finished_at'          => $this->finished_at,
            'final_score'          => $this->final_score,
            'application'          => $this->whenLoaded('application', fn() =>
            new ApplicationResource($this->application)
            ),
            'mentorship'           => $this->whenLoaded('mentorship', fn() =>
            new MentorshipResource($this->mentorship)
            ),
            'milestones'           => $this->whenLoaded('milestones', fn() =>
            MilestoneResource::collection($this->milestones)
            ),
            'created_at'           => $this->created_at,
        ];
    }
}
