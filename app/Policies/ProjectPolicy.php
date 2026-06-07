<?php

namespace App\Policies;

use App\Models\Project;
use App\Models\User;

class ProjectPolicy
{
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole(['super_admin', 'admin'])) {
            return true;
        }
        return null;
    }

    private function isMember(User $user, Project $project): bool
    {
        return $project->team && $project->team->members()
                ->where('users.id', $user->id)
                ->exists();
    }

    public function view(User $user, Project $project): bool
    {
        return $user->can('projects.view') || $this->isMember($user, $project);
    }

    public function create(User $user): bool
    {
        return $user->can('projects.create');
    }

    public function update(User $user, Project $project): bool
    {
        return $user->can('projects.edit') || $this->isMember($user, $project);
    }

    public function delete(User $user, Project $project): bool
    {
        return $user->can('projects.delete');
    }
}
