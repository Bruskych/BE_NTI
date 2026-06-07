<?php

namespace App\Policies;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChallengePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Challenge $challenge): bool
    {
        if ($challenge->isDraft()) {
            return $user->can('challenges.view-all') ||
                $user->organizations->contains($challenge->organization_id);
        }
        return true;
    }

    public function create(User $user): bool
    {
        //    РАЗРЕШЕНИЕ ЗАСИДИТЬ НУЖНО
        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return true;
        }

        $organization = $user->organizations()->first();
        return $organization && $organization->isActive();
    }

    public function update(User $user, Challenge $challenge): bool
    {
        if ($user->can('challenges.edit-all')) return true;

        return $user->organizations->contains($challenge->organization_id);
    }

    public function delete(User $user, Challenge $challenge): bool
    {
        //    РАЗРЕШЕНИЕ ЗАСИДИТЬ НУЖНО
        if ($user->hasAnyRole(['admin', 'super_admin'])) {
            return true;
        }

        return $user->organizations->contains($challenge->organization_id)
            && $challenge->isDraft();
    }

    public function restore(User $user, Challenge $challenge): bool
    {
        return false;
    }

    public function forceDelete(User $user, Challenge $challenge): bool
    {
        return false;
    }
}
