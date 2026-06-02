<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StudentProfileResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                        => $this->id,
            'study_program'             => $this->study_program,
            'year'                      => $this->year,
            'skills'                    => $this->skills_json,
            'cv_path'                   => $this->cv_path,
            'avg_grade'                 => $this->avg_grade,
            'has_carried_subjects'      => $this->has_carried_subjects,
            'eligibility_confirmed_at'  => $this->eligibility_confirmed_at,
        ];
    }
}
