<?php

namespace App\Policies;

use App\Models\Challenge;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ChallengePolicy
{
    public function viewAny(User $user): Response
    {
        return Response::allow();
    }

    public function view(User $user, Challenge $challenge): Response
    {
        if ($user->can('challenges.view-all')) {
            return Response::allow();
        }

        if ($challenge->isDraft()) {
            return $user->organizations()->where('organizations.id', $challenge->organization_id)->exists()
                ? Response::allow()
                : Response::deny('You cannot view this draft.');
        }

        return Response::allow();
    }

    public function create(User $user): Response
    {
        if (!$user->can('challenges.create')) {
            return Response::deny('You do not have permission to create challenges.');
        }

        return $user->organizations()->where('is_active', true)->exists()
            ? Response::allow()
            : Response::deny('Active organization is required to create a challenge.');
    }

    public function update(User $user, Challenge $challenge): Response
    {
        if ($user->can('challenges.edit-all')) {
            return Response::allow();
        }

        return $user->organizations()->where('organizations.id', $challenge->organization_id)->exists()
            ? Response::allow()
            : Response::deny('You do not own this challenge.');
    }

    public function delete(User $user, Challenge $challenge): Response
    {
        if (!$user->can('challenges.delete')) {
            return Response::deny('You do not have permission to delete challenges.');
        }

        if (!$challenge->isDraft()) {
            return Response::deny('Only drafts can be deleted.');
        }

        return $user->organizations()->where('organizations.id', $challenge->organization_id)->exists()
            ? Response::allow()
            : Response::deny('You do not own this challenge.');
    }
}
