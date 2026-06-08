<?php

namespace App\Policies;

use App\Models\Post;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class PostPolicy
{
    private function isAuthor(User $user, Post $post): bool
    {
        return $user->id === $post->author_id;
    }

    public function viewAny(User $user): Response
    {
        return Response::allow();
    }

    public function view(User $user, Post $post): Response
    {
        // Опубликованные посты видят все
        if ($post->is_published) {
            return Response::allow();
        }

        // Черновики — только автор или админ
        return ($this->isAuthor($user, $post) || $user->can('posts.view-all'))
            ? Response::allow()
            : Response::deny('This post is not published yet.');
    }

    public function create(User $user): Response
    {
        return $user->can('posts.create')
            ? Response::allow()
            : Response::deny('You do not have permission to create posts.');
    }

    public function update(User $user, Post $post): Response
    {
        if ($user->can('posts.edit-all')) return Response::allow();

        return ($user->can('posts.edit') && $this->isAuthor($user, $post))
            ? Response::allow()
            : Response::deny('You cannot edit this post.');
    }

    public function delete(User $user, Post $post): Response
    {
        if (!$user->can('posts.delete')) {
            return Response::deny('No permission to delete.');
        }

        return ($this->isAuthor($user, $post) || $user->can('posts.delete-all'))
            ? Response::allow()
            : Response::deny('You do not own this post.');
    }
}
