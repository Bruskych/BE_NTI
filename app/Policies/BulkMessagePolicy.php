<?php

namespace App\Policies;

use App\Models\BulkMessage;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/** Политика доступа к массовым рассылкам: только отправитель или администратор */
class BulkMessagePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole(['super_admin', 'admin']) ? true : null;
    }

    public function viewAny(User $user): Response
    {
        return Response::deny('You do not have permission to view bulk messages.');
    }

    public function view(User $user, BulkMessage $bulkMessage): Response
    {
        return $user->id === $bulkMessage->sender_id
            ? Response::allow()
            : Response::deny('You do not have access to this bulk message.');
    }

    public function create(User $user): Response
    {
        return $user->can('notifications.send-bulk')
            ? Response::allow()
            : Response::deny('You do not have permission to send bulk messages.');
    }
}
