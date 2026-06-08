<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PagePolicy
{
    public function before(User $user, string $ability): ?bool
    {
        return $user->hasRole(['super_admin', 'admin']) ? true : null;
    }

    public function viewAny(User $user): Response
    {
        return Response::allow();
    }

    public function view(User $user, Page $page): Response
    {
        // Опубликованные страницы видят все
        if ($page->is_published) return Response::allow();

        // Черновики — только редакторы с правом просмотра
        return $user->can('cms.pages.view')
            ? Response::allow()
            : Response::deny('This page is not published.');
    }

    public function create(User $user): Response
    {
        return $user->can('cms.pages.create')
            ? Response::allow()
            : Response::deny('No permission to create pages.');
    }

    public function update(User $user, Page $page): Response
    {
        return $user->can('cms.pages.edit')
            ? Response::allow()
            : Response::deny('No permission to edit pages.');
    }

    public function delete(User $user, Page $page): Response
    {
        return $user->can('cms.pages.delete')
            ? Response::allow()
            : Response::deny('No permission to delete pages.');
    }
}
