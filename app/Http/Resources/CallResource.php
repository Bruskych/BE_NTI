<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CallResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'title'                 => $this->title,
            'description'           => $this->description,
            'deadline'              => $this->deadline,
            'is_expired'            => $this->isExpired(),
            'status'                => $this->status,
            'budget'                => $this->budget,
            'program'               => $this->whenLoaded('program', fn() =>
            new ProgramResource($this->program)
            ),
            'evaluation_template'   => $this->whenLoaded('evaluationTemplate', fn() =>
            new EvaluationTemplateResource($this->evaluationTemplate)
            ),
            'specializations'       => $this->whenLoaded('specializations', fn() =>
            SpecializationResource::collection($this->specializations)
            ),
            'applications_count'    => $this->whenCounted('applications'),
            'created_at'            => $this->created_at,
        ];
    }
}
