<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;

class PageController extends Controller
{
    public function index()
    {
        $query = auth()->user()?->can('pages.view-all')
            ? Page::query()
            : Page::where('is_published', true);

        return PageResource::collection($query->get());
    }

    public function store(StorePageRequest $request): PageResource
    {
        $this->authorize('create', Page::class);
        $page = Page::create($request->validated());
        return new PageResource($page);
    }

    public function show(Page $page): PageResource
    {
        $this->authorize('view', $page);
        return new PageResource($page);
    }

    public function update(UpdatePageRequest $request, Page $page): PageResource
    {
        $this->authorize('update', $page);
        $page->update($request->validated());
        return new PageResource($page);
    }

    public function destroy(Page $page): JsonResponse
    {
        $this->authorize('delete', $page);
        $page->delete();
        return response()->json(['message' => 'Page deleted successfully'], 200);
    }
}
