<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Ресурс оценки заявки с итоговым баллом, баллами по критериям и рекомендацией */
class EvaluationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'application_id' => $this->application_id,
            'evaluator_id'   => $this->evaluator_id,
            'total_score'    => $this->total_score,
            'recommendation' => $this->recommendation,
            'comment'        => $this->comment,
            'scores'         => EvaluationScoreResource::collection($this->whenLoaded('scores')),
            'evaluator'      => new UserResource($this->whenLoaded('evaluator')),
            'created_at'     => $this->created_at,
            'updated_at'     => $this->updated_at,
        ];
    }
}
