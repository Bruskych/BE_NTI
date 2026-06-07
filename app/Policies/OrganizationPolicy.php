<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class OrganizationPolicy
{
    public function view(User $user, Organization $organization): bool
    {
        if ($user->can('organizations.view') || $organization->isActive()) {
            return true;
        }

        return $user->belongsToOrg($organization);
    }

    public function update(User $user, Organization $organization): bool
    {
        if ($user->can('organizations.edit')) {
            return true;
        }

        return $user->isOwnerOf($organization);
    }

    public function create(User $user): bool
    {
        return $user->can('organizations.create');
    }

    public function delete(User $user, Organization $organization): bool
    {
        return $user->can('organizations.delete') || $user->isOwnerOf($organization);
    }
}
