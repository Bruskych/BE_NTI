<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationAnswerResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'         => $this->id,
            'field_id'   => $this->field_id,
            'value_text' => $this->value_text,
            'value_json' => $this->value_json,
            'file_path'  => $this->file_path,
            'field'      => $this->whenLoaded('field', fn() =>
            new FormFieldResource($this->field)
            ),
        ];
    }
}
