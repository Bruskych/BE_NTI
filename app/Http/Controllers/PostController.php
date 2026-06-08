<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;

class PostController extends Controller
{
    public function index()
    {
        return PostResource::collection(Post::where('is_published', true)->latest()->get());
    }

    public function store(StorePostRequest $request): PostResource
    {
        $this->authorize('create', Post::class);

        $post = Post::create(array_merge($request->validated(), [
            'author_id' => auth()->id()
        ]));

        return new PostResource($post);
    }

    public function show(Post $post): PostResource
    {
        $this->authorize('view', $post);
        return new PostResource($post);
    }

    public function update(UpdatePostRequest $request, Post $post): PostResource
    {
        $this->authorize('update', $post);
        $post->update($request->validated());
        return new PostResource($post);
    }

    public function destroy(Post $post): JsonResponse
    {
        $this->authorize('delete', $post);
        $post->delete();
        return response()->json(['message' => 'Post deleted successfully'], 200);
    }
}
