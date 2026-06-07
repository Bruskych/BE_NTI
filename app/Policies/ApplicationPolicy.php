<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;

class ApplicationPolicy
{
    public function view(User $user, Application $application): bool
    {
        if ($user->can('applications.view-all')) return true;

        return $application->team->members()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Application $application): bool
    {
        return $application->team->members()->where('user_id', $user->id)->exists()
            && $application->canBeEdited();
    }

    public function submit(User $user, Application $application): bool
    {
        //return $user->can('applications.submit') &&
        //    $application->team->leader_id === $user->id &&
        //    $application->canBeSubmitted();
        //    РАЗРЕШЕНИЕ ЗАСИДИТЬ НУЖНО
        return $application->team->leader_id === $user->id &&
            $application->canBeSubmitted();
    }

    public function changeStatus(User $user, Application $application): bool
    {
        return $user->can('applications.change-status');
    }
}
