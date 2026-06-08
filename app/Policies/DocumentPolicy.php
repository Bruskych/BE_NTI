<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Document;

class DocumentPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Document $document): bool
    {
        // Public documents visible to all
        if ($document->classification === Document::CLASSIFICATION_PUBLIC) {
            return true;
        }

        // Own documents
        if ($document->uploaded_by === $user->id) {
            return true;
        }

        // Application documents
        if ($document->application_id) {
            $application = $document->application;
            if ($application?->team?->members?->contains('id', $user->id)) {
                return true;
            }
        }

        // Project documents
        if ($document->project_id) {
            $project = $document->project;
            if ($project?->team?->members?->contains('id', $user->id)) {
                return true;
            }
        }

        // Admin can view all
        return $user->hasRole(['admin', 'super_admin']);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Document $document): bool
    {
        return $user->id === $document->uploaded_by || $user->hasRole(['admin', 'super_admin']);
    }

    public function delete(User $user, Document $document): bool
    {
        return $user->id === $document->uploaded_by || $user->hasRole(['admin', 'super_admin']);
    }

    public function download(User $user, Document $document): bool
    {
        return $this->view($user, $document);
    }
}
