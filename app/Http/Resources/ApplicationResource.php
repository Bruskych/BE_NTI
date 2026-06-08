<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'status'           => $this->status,
            'is_program_a'     => $this->isProgramA(),
            'is_program_b'     => $this->isProgramB(),
            'can_be_edited'    => $this->canBeEdited(),
            'can_be_submitted' => $this->canBeSubmitted(),
            'total_score'      => $this->total_score,
            'decision_comment' => $this->decision_comment,
            'submitted_at'     => $this->submitted_at,
            'approved_at'      => $this->approved_at,
            'rejected_at'      => $this->rejected_at,
            'program'          => $this->whenLoaded('program', fn() =>
            new ProgramResource($this->program)
            ),
            'call'             => $this->whenLoaded('call', fn() =>
            new CallResource($this->call)
            ),
            'challenge'        => $this->whenLoaded('challenge', fn() =>
            new ChallengeResource($this->challenge)
            ),
            'team'             => $this->whenLoaded('team', fn() =>
            new TeamResource($this->team)
            ),
            'organization'     => $this->whenLoaded('organization', fn() =>
            new OrganizationResource($this->organization)
            ),
            'history'          => $this->whenLoaded('history', fn() =>
            ApplicationHistoryResource::collection($this->history)
            ),
            'project'          => $this->whenLoaded('project', fn() =>
            new ProjectResource($this->project)
            ),
            'answers'             => $this->whenLoaded('answers', fn() =>
            ApplicationAnswerResource::collection($this->answers)
            ),
            'pairing_submissions' => $this->whenLoaded('pairingSubmissions', fn() =>
            ApplicationPairingSubmissionResource::collection($this->pairingSubmissions)
            ),
            'created_at'       => $this->created_at,
        ];
    }
}
