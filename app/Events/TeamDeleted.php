<?php

namespace App\Events;

use Illuminate\Support\Collection;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeamDeleted
{
    use Dispatchable, SerializesModels;

    public function __construct(public Collection $members, public string $teamName)
    {
    }
}
