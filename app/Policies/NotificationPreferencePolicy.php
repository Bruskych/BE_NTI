<?php

namespace App\Policies;

use App\Models\NotificationPreference;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/** Политика доступа к настройкам уведомлений: только сам пользователь */
class NotificationPreferencePolicy
{

    public function view(User $user, NotificationPreference $preference): bool
    {
        return $user->id === $preference->user_id;
    }

    public function update(User $user, NotificationPreference $preference): bool
    {
        return $user->id === $preference->user_id;
    }
}
