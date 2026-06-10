<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

/** Политика доступа к журналу экспортов: только администраторы */
class ExportsLogPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole(['super_admin', 'admin']) ? true : null;
    }

    public function viewAny(User $user): Response
    {
        return Response::deny('You do not have permission to view exports.');
    }
}
