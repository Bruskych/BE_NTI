<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/** Политика доступа к организациям: публичное чтение активных, редактирование для владельцев */
class OrganizationPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole(['super_admin', 'admin']) ? true : null;
    }

    public function viewAny(User $user): Response
    {
        return Response::allow();
    }

    public function view(User $user, Organization $organization): Response
    {
        if ($user->can('organizations.view') || $organization->isActive() || $user->belongsToOrg($organization)) {
            return Response::allow();
        }

        return Response::deny('You do not have access to this organization.');
    }

    public function create(User $user): Response
    {
        return $user->can('organizations.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create organizations.');
    }

    public function update(User $user, Organization $organization): Response
    {
        return ($user->can('organizations.edit') || $user->isOwnerOf($organization))
            ? Response::allow()
            : Response::deny('You do not have permission to update this organization.');
    }

    public function delete(User $user, Organization $organization): Response
    {
        return ($user->can('organizations.delete') || $user->isOwnerOf($organization))
            ? Response::allow()
            : Response::deny('You do not have permission to delete this organization.');
    }

    public function manageMembers(User $user, Organization $organization): Response
    {
        return ($user->can('organizations.edit') || $user->isOwnerOf($organization))
            ? Response::allow()
            : Response::deny('Only the organization owner can manage members.');
    }
}
