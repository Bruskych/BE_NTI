<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Document;
use Illuminate\Auth\Access\Response;

class DocumentPolicy
{
    private function isOwner(User $user, Document $document): bool
    {
        return $user->id === $document->uploaded_by;
    }

    private function isRelatedTeamMember(User $user, Document $document): bool
    {
        if ($document->application_id && $document->application?->team?->members?->contains('id', $user->id)) {
            return true;
        }

        if ($document->project_id && $document->project?->team?->members?->contains('id', $user->id)) {
            return true;
        }

        return false;
    }

    public function viewAny(User $user): Response
    {
        return Response::allow();
    }

    public function view(User $user, Document $document): Response
    {
        if ($document->classification === Document::CLASSIFICATION_PUBLIC) {
            return Response::allow();
        }

        if ($this->isOwner($user, $document) || $this->isRelatedTeamMember($user, $document) || $user->can('documents.view-all')) {
            return Response::allow();
        }

        return Response::deny('You do not have access to this document.');
    }

    public function create(User $user): Response
    {
        return Response::allow();
    }

    public function update(User $user, Document $document): Response
    {
        if ($this->isOwner($user, $document) || $user->can('documents.edit')) {
            return Response::allow();
        }

        return Response::deny('You cannot edit this document.');
    }

    public function delete(User $user, Document $document): Response
    {
        if ($this->isOwner($user, $document) || $user->can('documents.delete-all')) {
            return Response::allow();
        }

        return Response::deny('You cannot delete this document.');
    }

    public function download(User $user, Document $document): Response
    {
        return $this->view($user, $document);
    }
}
