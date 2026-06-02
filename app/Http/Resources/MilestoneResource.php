<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MilestoneResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'title'                 => $this->title,
            'description'           => $this->description,
            'deadline'              => $this->deadline,
            'status'                => $this->status,
            'completion_percentage' => $this->completion_percentage,
            'is_completed'          => $this->isCompleted(),
            'is_approved'           => $this->isApproved(),
            'is_overdue'            => $this->isOverdue(),
            'completed_at'          => $this->completed_at,
            'approved_by'           => $this->whenLoaded('approvedBy', fn() =>
            new UserResource($this->approvedBy)
            ),
            'consultations'         => $this->whenLoaded('consultations', fn() =>
            ConsultationResource::collection($this->consultations)
            ),
            'created_at'            => $this->created_at,
        ];
    }
}
