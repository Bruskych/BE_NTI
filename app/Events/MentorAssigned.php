<?php

namespace App\Events;

use App\Models\Mentorship;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MentorAssigned
{
    use Dispatchable, SerializesModels;

    public function __construct(public Mentorship $mentorship)
    {
    }
}
