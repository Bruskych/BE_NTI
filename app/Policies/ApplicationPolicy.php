<?php

namespace App\Policies;

use App\Models\Application;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ApplicationPolicy
{

    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole(['super_admin', 'admin']) ? true : null;
    }

    private function isMember(User $user, Application $application): bool
    {
        // Проверяем, есть ли у пользователя связь с этой командой
        return $user->teams()->where('teams.id', $application->team_id)->exists();
    }

    public function viewAny(User $user): Response
    {
        return $user->can('applications.view-all')
            ? Response::allow()
            : Response::deny('You do not have permission to view the list of applications.');
    }

    public function view(User $user, Application $application): Response
    {
        if ($user->can('applications.view-all') || $this->isMember($user, $application)) {
            return Response::allow();
        }

        return Response::deny('You do not have access to this application.');
    }

    public function create(User $user): Response
    {
        $hasPermission = $user->can('applications.create');
        $isLeader = $user->teams()->where('leader_id', $user->id)->exists();

        return ($hasPermission && $isLeader)
            ? Response::allow()
            : Response::deny('Only team leaders with correct permissions can create applications.');
    }

    public function update(User $user, Application $application): Response
    {
        if (!$this->isMember($user, $application)) {
            return Response::deny('You are not a member of the team associated with this application.');
        }

        return $application->canBeEdited()
            ? Response::allow()
            : Response::deny('You cannot edit this application in its current status.');
    }

    public function delete(User $user, Application $application): Response
    {
        if (!$user->can('applications.delete')) {
            return Response::deny('You lack the delete permission.');
        }

        return ($application->status === Application::STATUS_DRAFT)
            ? Response::allow()
            : Response::deny('You cannot delete an application that has already been submitted.');
    }

    public function submit(User $user, Application $application): Response
    {
        if ($user->can('applications.submit') &&
            $application->team->leader_id === $user->id &&
            $application->canBeSubmitted()) {
            return Response::allow();
        }

        return Response::deny('You cannot submit this application.');
    }

    public function changeStatus(User $user, Application $application): Response
    {
        return $user->can('applications.change-status')
            ? Response::allow()
            : Response::deny('You lack permission to change status.');
    }
}
