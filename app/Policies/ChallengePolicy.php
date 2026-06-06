<?php

namespace App\Policies;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChallengePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Challenge $challenge): bool
    {
        if ($challenge->isDraft()) {
            return $user->hasAnyRole(['admin', 'super_admin']) ||
                $user->organizations()->where('organizations.id', $challenge->organization_id)->exists();
        }
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return true;
        }

        $organization = $user->organizations()->first();
        return $organization && $organization->isActive();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Challenge $challenge): bool
    {
        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return true;
        }

        return $user->organizations()
            ->where('organizations.id', $challenge->organization_id)
            ->exists();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Challenge $challenge): bool
    {
        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Challenge $challenge): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Challenge $challenge): bool
    {
        return false;
    }
}
