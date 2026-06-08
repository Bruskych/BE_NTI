<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class CompanyApplicationResource extends JsonResource
{
    public function toArray($request)
    {
        $org = $this->organization;
        $owner = $this->team ? $this->team->leader : null;

        return [
            'application_id' => $this->id,
            'status'         => $this->status,
            'submitted_at'   => $this->submitted_at ? Carbon::parse($this->submitted_at)->toIso8601String() : null,
            'company_name'   => $org ? $org->name : 'Unknown company',
            'company_tax_id' => $org ? $org->tax_id : 'Without TAX ID',
            'sector'         => $org ? $org->sector : 'Unregistered sector',
            'website_link'   => $org ? $org->website_link : null,
            'description'    => $org ? $org->description : 'No description',
            'owner_name'     => $owner ? $owner->name : 'Unknown representative',
            'owner_email'    => $owner ? $owner->email : 'No email',
            'user_id'        => $owner ? $owner->id : null
        ];
    }
}
