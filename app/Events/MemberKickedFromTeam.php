<?php

namespace App\Events;

use App\Models\User;
use App\Models\Team;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MemberKickedFromTeam
{
    use Dispatchable, SerializesModels;

    public function __construct(public User $user, public Team $team)
    {
    }
}
