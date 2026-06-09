<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

/** Политика доступа к публикациям блога: опубликованные видны всем, редактирование — только автору */
class PostPolicy
{
    private function isAuthor(User $user, Post $post): bool
    {
        return $user->id === $post->author_id;
    }

    public function before(?User $user, string $ability): ?bool
    {
        return $user?->hasRole(['super_admin', 'admin']) ? true : null;
    }

    public function viewAny(?User $user): Response
    {
        return Response::allow();
    }

    public function view(?User $user, Post $post): Response
    {
        // Опубликованные посты видят все, включая гостей
        if ($post->is_published) {
            return Response::allow();
        }

        // Черновики — только автор или редактор с правом просмотра
        return ($user && ($this->isAuthor($user, $post) || $user->can('cms.posts.view')))
            ? Response::allow()
            : Response::deny('This post is not published yet.');
    }

    public function create(User $user): Response
    {
        return $user->can('cms.posts.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create posts.');
    }

    public function update(User $user, Post $post): Response
    {
        return ($user->can('cms.posts.edit') && $this->isAuthor($user, $post))
            ? Response::allow()
            : Response::deny('You cannot edit this post.');
    }

    public function delete(User $user, Post $post): Response
    {
        return ($user->can('cms.posts.delete') && $this->isAuthor($user, $post))
            ? Response::allow()
            : Response::deny('You cannot delete this post.');
    }
}
