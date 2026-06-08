<?php

namespace App\Policies;

use App\Models\Mentorship;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MentorshipPolicy
{
    private function isMentor(User $user, Mentorship $mentorship): bool
    {
        return $user->id === $mentorship->mentor_id;
    }

    public function viewAny(User $user): Response
    {
        return $user->can('mentorships.view-any')
            ? Response::allow()
            : Response::deny('You do not have permission to list mentorships.');
    }

    public function view(User $user, Mentorship $mentorship): Response
    {
        if ($user->can('mentorships.view-all') || $this->isMentor($user, $mentorship)) {
            return Response::allow();
        }

        return $mentorship->project->members->contains($user->id)
            ? Response::allow()
            : Response::deny('You do not have access to this mentorship.');
    }

    public function create(User $user): Response
    {
        return $user->can('mentorships.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create mentorships.');
    }

    public function update(User $user, Mentorship $mentorship): Response
    {
        if ($user->can('mentorships.edit-all')) return Response::allow();

        return ($user->can('mentorships.edit') && $this->isMentor($user, $mentorship))
            ? Response::allow()
            : Response::deny('You cannot edit this mentorship.');
    }

    public function delete(User $user, Mentorship $mentorship): Response
    {
        return $user->can('mentorships.delete')
            ? Response::allow()
            : Response::deny('No permission to delete mentorships.');
    }
}
