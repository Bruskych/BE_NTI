<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BulkMessageResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'sender_id'       => $this->sender_id,
            'target_group'    => $this->target_group,
            'subject'         => $this->subject,
            'body'            => $this->body,
            'recipient_count' => $this->whenCounted('recipients'),
            'is_sent'         => $this->isSent(),
            'sent_at'         => $this->sent_at,
            'sender'          => new UserResource($this->whenLoaded('sender')),
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
