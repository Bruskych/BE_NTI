<?php

namespace App\Policies;

use App\Models\Milestone;
use App\Models\User;

class MilestonePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }

        return null;
    }

    private function isMember(User $user, Milestone $milestone): bool
    {
        return $milestone->project->team->members()
            ->where('users.id', $user->id)
            ->exists();
    }

    public function view(User $user, Milestone $milestone): bool
    {
        return $user->can('milestones.view') || $this->isMember($user, $milestone);
    }

    public function create(User $user): bool
    {
        return $user->can('milestones.create');
    }

    public function update(User $user, Milestone $milestone): bool
    {
        if ($user->can('milestones.edit')) {
            return true;
        }

        return $this->isMember($user, $milestone);
    }

    public function approve(User $user, Milestone $milestone): bool
    {
        if (!$user->can('milestones.approve')) {
            return false;
        }

        if ($this->isMember($user, $milestone)) {
            return false;
        }

        return true;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('milestones.view');
    }
}
