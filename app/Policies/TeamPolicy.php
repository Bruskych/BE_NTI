<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class TeamPolicy
{
    private function isLeader(User $user, Team $team): bool
    {
        return (int) $user->id === (int) $team->leader_id;
    }

    public function viewAny(User $user): Response
    {
        return Response::allow();
    }

    public function view(User $user, Team $team): Response
    {
        return ($this->isLeader($user, $team) || $team->hasMember($user->id))
            ? Response::allow()
            : Response::deny('You are not a member of this team.');
    }

    public function create(User $user): Response
    {
        return !$user->teams()->exists()
            ? Response::allow()
            : Response::deny('You are already a member of a team.');
    }

    public function update(User $user, Team $team): Response
    {
        return $this->isLeader($user, $team)
            ? Response::allow()
            : Response::deny('Only the team leader can update team settings.');
    }

    public function delete(User $user, Team $team): Response
    {
        return $this->isLeader($user, $team)
            ? Response::allow()
            : Response::deny('Only the team leader can delete the team.');
    }

    public function invite(User $user, Team $team): Response
    {
        return $this->isLeader($user, $team)
            ? Response::allow()
            : Response::deny('Only the team leader can send invitations.');
    }

    public function removeMember(User $user, Team $team): Response
    {
        return $this->isLeader($user, $team)
            ? Response::allow()
            : Response::deny('Only the team leader can remove members.');
    }
}
