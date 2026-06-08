<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PagePolicy
{
    public function viewAny(User $user): Response
    {
        return Response::allow();
    }

    public function view(User $user, Page $page): Response
    {
        // Опубликованные страницы видят все
        if ($page->is_published) return Response::allow();

        // Черновики — только админы/редакторы с правами
        return $user->can('pages.view-all')
            ? Response::allow()
            : Response::deny('This page is not published.');
    }

    public function create(User $user): Response
    {
        return $user->can('pages.create')
            ? Response::allow()
            : Response::deny('No permission to create pages.');
    }

    public function update(User $user, Page $page): Response
    {
        return $user->can('pages.edit')
            ? Response::allow()
            : Response::deny('No permission to edit pages.');
    }

    public function delete(User $user, Page $page): Response
    {
        return $user->can('pages.delete')
            ? Response::allow()
            : Response::deny('No permission to delete pages.');
    }
}
