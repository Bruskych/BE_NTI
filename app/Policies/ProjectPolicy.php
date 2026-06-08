<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ProjectPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole(['super_admin', 'admin']) ? true : null;
    }

    private function isMember(User $user, Project $project): bool
    {
        // Используем связь через метод team(), чтобы избежать загрузки всей коллекции
        return $project->team && $project->team->members()
                ->where('users.id', $user->id)
                ->exists();
    }

    public function view(User $user, Project $project): Response
    {
        return ($user->can('projects.view') || $this->isMember($user, $project))
            ? Response::allow()
            : Response::deny('You do not have access to this project.');
    }

    public function create(User $user): Response
    {
        return $user->can('projects.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create projects.');
    }

    public function update(User $user, Project $project): Response
    {
        return ($user->can('projects.edit') || $this->isMember($user, $project))
            ? Response::allow()
            : Response::deny('You do not have permission to update this project.');
    }

    public function delete(User $user, Project $project): Response
    {
        return $user->can('projects.delete')
            ? Response::allow()
            : Response::deny('You do not have permission to delete this project.');
    }
}
