<?php

namespace App\Policies;

use App\Models\Call;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/** Политика доступа к конкурсным отборам: публичное чтение, управление только для staff */
class CallPolicy
{
    public function before(?User $user, string $ability): ?bool
    {
        return $user?->hasRole(['super_admin', 'admin']) ? true : null;
    }

    public function viewAny(User $user): Response
    {
        return Response::allow();
    }

    public function view(User $user, Call $call): Response
    {
        if ($call->status !== 'draft' || $user->can('calls.view')) {
            return Response::allow();
        }

        return Response::deny('You do not have access to this draft call.');
    }

    public function create(User $user): Response
    {
        return $user->can('calls.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create calls.');
    }

    public function update(User $user, Call $call): Response
    {
        return $user->can('calls.edit')
            ? Response::allow()
            : Response::deny('You cannot edit this call.');
    }

    public function delete(User $user, Call $call): Response
    {
        if (!$user->can('calls.delete')) {
            return Response::deny('No permission to delete.');
        }

        return $call->isDraft()
            ? Response::allow()
            : Response::deny('Only draft calls can be deleted.');
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
