<?php

namespace App\Policies;

use App\Models\Call;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CallPolicy
{

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(['admin', 'super_admin']);
    }

    private function isOrganizationOwner(User $user, Call $call): bool
    {
        return $user->organizations->contains('id', $call->organization_id);
    }

    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Call $call): bool
    {
        if ($this->isAdmin($user)) return true;

        if ($call->status === 'draft') {
            return $this->isOrganizationOwner($user, $call);
        }

        return true;
    }

    public function create(User $user): bool
    {
        if ($this->isAdmin($user)) return true;

        $organization = $user->organizations()->first();
        return $organization && $organization->isActive();
    }

    public function update(User $user, Call $call): bool
    {
        if ($this->isAdmin($user)) return true;

        return $this->isOrganizationOwner($user, $call);
    }

    public function delete(User $user, Call $call): bool
    {
        if ($this->isAdmin($user)) return true;

        return $this->isOrganizationOwner($user, $call) && $call->status === 'draft';
    }

    public function restore(User $user, Call $call): bool
    {
        return $this->isAdmin($user);
    }
}
