<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** Ресурс записи истории изменений статуса заявки */
class ApplicationHistoryResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'old_status'   => $this->old_status,
            'new_status'   => $this->new_status,
            'comment'      => $this->comment,
            'changed_by'   => $this->whenLoaded('changedBy', fn() =>
            new UserResource($this->changedBy)
            ),
            'created_at'   => $this->created_at,
        ];
    }
}
