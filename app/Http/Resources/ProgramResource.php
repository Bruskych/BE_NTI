<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Ресурс программы инкубатора с типом и статусом активности */
class ProgramResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'          => $this->id,
            'name'        => $this->name,
            'type'        => $this->type,
            'description' => $this->description,
            'is_active'   => $this->is_active,
            'is_grant'    => $this->isGrant(),
            'is_practice' => $this->isPractice(),
        ];
    }
}
