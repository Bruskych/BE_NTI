<?php

namespace App\Policies;

use App\Models\Milestone;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MilestonePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Milestone $milestone): bool
    {
        return $milestone->project->team->members->contains($user->id) || $user->isStaff();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return false;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Milestone $milestone): bool
    {
        return $milestone->project->team->members->contains($user->id);
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Milestone $milestone): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Milestone $milestone): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Milestone $milestone): bool
    {
        return false;
    }
    public function approve(User $user, Milestone $milestone): bool
    {
        if (!$user->hasAnyRole(['mentor', 'evaluator', 'admin'])) {
            return false;
        }

        if ($milestone->project->team->members->contains($user->id)) {
            return false;
        }

        return true;
    }
}
