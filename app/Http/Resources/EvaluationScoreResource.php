<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EvaluationScoreResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'criteria_id' => $this->criteria_id,
            'score'       => $this->score,
            'comment'     => $this->comment,
            'criteria'    => new EvaluationCriteriaResource($this->whenLoaded('criteria')),
        ];
    }
}
