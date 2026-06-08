<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DocumentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'              => $this->id,
            'type'            => $this->type,
            'file_name'       => $this->file_name,
            'mime_type'       => $this->mime_type,
            'size'            => $this->size,
            'size_kb'         => $this->fileSizeInKb(),
            'version'         => $this->version,
            'classification'  => $this->classification,
            'application_id'  => $this->application_id,
            'project_id'      => $this->project_id,
            'milestone_id'    => $this->milestone_id,
            'uploaded_by'     => $this->uploaded_by,
            'application'     => $this->whenLoaded('application', fn() =>
                new ApplicationResource($this->application)
            ),
            'project'         => $this->whenLoaded('project', fn() =>
                new ProjectResource($this->project)
            ),
            'created_at'      => $this->created_at,
            'updated_at'      => $this->updated_at,
        ];
    }
}
