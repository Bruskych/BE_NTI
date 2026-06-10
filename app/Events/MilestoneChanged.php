<?php

namespace App\Events;

use App\Models\Milestone;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MilestoneChanged
{
    use Dispatchable, SerializesModels;

    public function __construct(public Milestone $milestone, public string $action)
    {
    }
}
