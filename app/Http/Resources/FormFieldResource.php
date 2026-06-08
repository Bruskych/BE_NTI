<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormFieldResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'               => $this->id,
            'program_id'       => $this->program_id,
            'call_id'          => $this->call_id,
            'name'             => $this->name,
            'label'            => $this->label,
            'type'             => $this->type,
            'required'         => $this->required,
            'options'          => $this->options_json,
            'validation_rules' => $this->validation_rules,
            'order'            => $this->order,
        ];
    }
}
