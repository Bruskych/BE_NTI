<?php

namespace App\Events;

use App\Models\Consultation;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ConsultationScheduled
{
    use Dispatchable, SerializesModels;

    public function __construct(public Consultation $consultation)
    {
    }
}
