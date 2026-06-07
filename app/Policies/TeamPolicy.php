<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    private function isLeader(User $user, Team $team): bool
    {
        return (int) $user->id === (int) $team->leader_id;
    }

    public function view(User $user, Team $team): bool
    {
        return $this->isLeader($user, $team) || $team->members()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Team $team): bool
    {
        return $this->isLeader($user, $team);
    }

    public function invite(User $user, Team $team): bool
    {
        return $this->isLeader($user, $team);
    }

    public function delete(User $user, Team $team): bool
    {
        return $this->isLeader($user, $team);
    }
}
