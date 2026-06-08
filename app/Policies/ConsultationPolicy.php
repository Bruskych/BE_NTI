<?php

namespace App\Policies;

use App\Models\Consultation;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ConsultationPolicy
{
    private function isOwner(User $user, Consultation $consultation): bool
    {
        return (int) $user->id === (int) $consultation->mentor_id;
    }

    public function viewAny(User $user): Response
    {
        return ($user->can('consultations.view') || $user->can('consultations.view-own'))
            ? Response::allow()
            : Response::deny('You do not have permission to view consultations.');
    }

    public function view(User $user, Consultation $consultation): Response
    {
        // Доступ открыт если:
        // 1 У пользователя есть глобальное право view (видит все консультации платформы)
        // 2 ИЛИ у пользователя есть право view-own И он ментор этой консультации либо участник команды проекта

        if ($user->can('consultations.view')) {
            return Response::allow();
        }

        $isTeamMember = $consultation->mentorship->project->team->hasMember($user->id);

        if ($user->can('consultations.view-own') && ($this->isOwner($user, $consultation) || $isTeamMember)) {
            return Response::allow();
        }

        return Response::deny('You do not have access to this consultation.');
    }

    public function create(User $user): Response
    {
        return $user->can('consultations.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create consultations.');
    }

    public function update(User $user, Consultation $consultation): Response
    {
        return ($user->can('consultations.edit-own') && $this->isOwner($user, $consultation))
            ? Response::allow()
            : Response::deny('Only the mentor can update this consultation.');
    }

    public function delete(User $user, Consultation $consultation): Response
    {
        return ($user->can('consultations.delete-own') && $this->isOwner($user, $consultation))
            ? Response::allow()
            : Response::deny('Only the mentor can delete this consultation.');
    }
}
