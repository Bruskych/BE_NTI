<?php

namespace App\Policies;

use App\Models\Milestone;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/** Политика доступа к контрольным точкам: участники команды управляют, сторонний staff подтверждает */
class MilestonePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole(['super_admin', 'admin']) ? true : null;
    }

    private function isMember(User $user, Milestone $milestone): bool
    {
        return $milestone->project->team->members()
            ->where('users.id', $user->id)
            ->exists();
    }

    public function view(User $user, Milestone $milestone): Response
    {
        return ($user->can('milestones.view') || $this->isMember($user, $milestone))
            ? Response::allow()
            : Response::deny('Access denied.');
    }

    public function create(User $user): Response
    {
        return $user->can('milestones.create')
            ? Response::allow()
            : Response::deny('No permission to create milestones.');
    }

    public function update(User $user, Milestone $milestone): Response
    {
        return ($user->can('milestones.edit') || $this->isMember($user, $milestone))
            ? Response::allow()
            : Response::deny('You cannot edit this milestone.');
    }

    public function approve(User $user, Milestone $milestone): Response
    {
        if (!$user->can('milestones.approve')) {
            return Response::deny('You lack the approve permission.');
        }

        return $this->isMember($user, $milestone)
            ? Response::deny('You cannot approve your own project milestone.')
            : Response::allow();
    }
}
