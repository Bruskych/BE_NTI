<?php

namespace App\Policies;

use App\Models\BulkMessage;
use App\Models\User;

class BulkMessagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }

    public function view(User $user, BulkMessage $bulkMessage): bool
    {
        return $user->id === $bulkMessage->sender_id || $user->hasRole(['admin', 'super_admin']);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(['admin', 'super_admin']);
    }
}
