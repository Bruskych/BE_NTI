<?php

namespace App\Policies;

use App\Models\Call;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class CallPolicy
{
    private function isOrganizationOwner(User $user, Call $call): bool
    {
        return $user->organizations()->where('organizations.id', $call->organization_id)->exists();
    }

    public function viewAny(User $user): Response
    {
        return Response::allow();
    }

    public function view(User $user, Call $call): Response
    {
        if ($user->can('calls.view-all') || $call->status !== 'draft') {
            return Response::allow();
        }

        return $this->isOrganizationOwner($user, $call)
            ? Response::allow()
            : Response::deny('You do not have access to this draft call.');
    }

    public function create(User $user): Response
    {
        return $user->can('calls.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create calls.');
    }

    public function update(User $user, Call $call): Response
    {
        if ($user->can('calls.edit-all')) return Response::allow();

        return ($user->can('calls.edit') && $this->isOrganizationOwner($user, $call))
            ? Response::allow()
            : Response::deny('You cannot edit this call.');
    }

    public function delete(User $user, Call $call): Response
    {
        if (!$user->can('calls.delete')) {
            return Response::deny('No permission to delete.');
        }

        return ($call->isDraft() && $this->isOrganizationOwner($user, $call))
            ? Response::allow()
            : Response::deny('Only draft calls owned by your organization can be deleted.');
    }

    public function open(User $user, Call $call): Response
    {
        return $user->can('calls.open') ? Response::allow() : Response::deny();
    }

    public function close(User $user, Call $call): Response
    {
        return $user->can('calls.close') ? Response::allow() : Response::deny();
    }
}
