<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class StudentApplicationResource extends JsonResource
{
    public function toArray($request)
    {
        $leader = $this->team ? $this->team->leader : null;

        return [
            'application_id' => $this->id,
            'status'         => $this->status,
            'submitted_at'   => $this->submitted_at ? Carbon::parse($this->submitted_at)->toIso8601String() : null,
            'student_name'   => $leader ? $leader->name : 'Application without a leader',
            'student_email'  => $leader ? $leader->email : 'Team ID: ' . $this->team_id,
            'user_id'        => $leader ? $leader->id : null
        ];
    }
}
