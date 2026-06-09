<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/** Контроллер блог-постов: CRUD для публикаций CMS */
class PostController extends Controller
{
    /** Возвращает список опубликованных блог-постов */
    #[OA\Get(
        path: '/posts',
        summary: 'List published posts',
        tags: ['Posts'],
        responses: [
            new OA\Response(response: 200, description: 'List of published posts'),
        ]
    )]
    public function index()
    {
        return PostResource::collection(Post::where('is_published', true)->latest()->get());
    }

    /** Создаёт новый блог-пост от имени текущего пользователя */
    #[OA\Post(
        path: '/posts',
        summary: 'Create a blog post (authored by the current user)',
        tags: ['Posts'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Post created'),
            new OA\Response(response: 403, description: 'Not authorized to create posts'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StorePostRequest $request): PostResource
    {
        $this->authorize('create', Post::class);

        $post = Post::create(array_merge($request->validated(), [
            'author_id' => auth()->id()
        ]));

        return new PostResource($post);
    }

    /** Возвращает один блог-пост */
    #[OA\Get(
        path: '/posts/{post}',
        summary: 'Get a single post',
        tags: ['Posts'],
        parameters: [
            new OA\Parameter(name: 'post', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Post detail'),
            new OA\Response(response: 403, description: 'Not authorized to view this post'),
            new OA\Response(response: 404, description: 'Post not found'),
        ]
    )]
    public function show(Post $post): PostResource
    {
        $this->authorize('view', $post);
        return new PostResource($post);
    }

    /** Обновляет блог-пост */
    #[OA\Put(
        path: '/posts/{post}',
        summary: 'Update a blog post',
        tags: ['Posts'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'post', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Post updated'),
            new OA\Response(response: 403, description: 'Not authorized to update this post'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdatePostRequest $request, Post $post): PostResource
    {
        $this->authorize('update', $post);
        $post->update($request->validated());
        return new PostResource($post);
    }

    /** Удаляет блог-пост */
    #[OA\Delete(
        path: '/posts/{post}',
        summary: 'Delete a blog post',
        tags: ['Posts'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'post', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Post deleted'),
            new OA\Response(response: 403, description: 'Not authorized to delete this post'),
        ]
    )]
    public function destroy(Post $post): JsonResponse
    {
        $this->authorize('delete', $post);
        $post->delete();
        return $this->apiJson(['message' => 'Post deleted successfully'], 200);
    }
}
