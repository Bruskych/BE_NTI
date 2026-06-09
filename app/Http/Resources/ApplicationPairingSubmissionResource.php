<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Ресурс документа парного отбора Programme B */
class ApplicationPairingSubmissionResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'        => $this->id,
            'type'      => $this->type,
            'file_path' => $this->file_path,
            'notes'     => $this->notes,
        ];
    }
}
