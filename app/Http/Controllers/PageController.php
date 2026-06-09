<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePageRequest;
use App\Http\Requests\UpdatePageRequest;
use App\Http\Resources\PageResource;
use App\Models\Page;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/** Контроллер CMS-страниц: создание, просмотр, обновление и удаление страниц */
class PageController extends Controller
{
    /** Возвращает список страниц: опубликованные для всех, все — для сотрудников */
    #[OA\Get(
        path: '/pages',
        summary: 'List pages (published only for visitors, all for staff with cms.pages.view)',
        tags: ['Pages'],
        responses: [
            new OA\Response(response: 200, description: 'List of pages'),
        ]
    )]
    public function index()
    {
        $user = auth()->user();
        $query = ($user?->hasRole(['super_admin', 'admin']) || $user?->can('cms.pages.view'))
            ? Page::query()
            : Page::where('is_published', true);

        return PageResource::collection($query->get());
    }

    /** Создаёт новую CMS-страницу */
    #[OA\Post(
        path: '/pages',
        summary: 'Create a CMS page',
        tags: ['Pages'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Page created'),
            new OA\Response(response: 403, description: 'Not authorized to create pages'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StorePageRequest $request): PageResource
    {
        $this->authorize('create', Page::class);
        $page = Page::create($request->validated());
        return new PageResource($page);
    }

    /** Возвращает одну страницу по идентификатору */
    #[OA\Get(
        path: '/pages/{page}',
        summary: 'Get a single page',
        tags: ['Pages'],
        parameters: [
            new OA\Parameter(name: 'page', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Page detail'),
            new OA\Response(response: 403, description: 'Not authorized to view this page'),
            new OA\Response(response: 404, description: 'Page not found'),
        ]
    )]
    public function show(Page $page): PageResource
    {
        $this->authorize('view', $page);
        return new PageResource($page);
    }

    /** Обновляет содержимое CMS-страницы */
    #[OA\Put(
        path: '/pages/{page}',
        summary: 'Update a CMS page',
        tags: ['Pages'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Page updated'),
            new OA\Response(response: 403, description: 'Not authorized to update this page'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdatePageRequest $request, Page $page): PageResource
    {
        $this->authorize('update', $page);
        $page->update($request->validated());
        return new PageResource($page);
    }

    /** Удаляет CMS-страницу */
    #[OA\Delete(
        path: '/pages/{page}',
        summary: 'Delete a CMS page',
        tags: ['Pages'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'page', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Page deleted'),
            new OA\Response(response: 403, description: 'Not authorized to delete this page'),
        ]
    )]
    public function destroy(Page $page): JsonResponse
    {
        $this->authorize('delete', $page);
        $page->delete();
        return $this->apiJson(['message' => 'Page deleted successfully'], 200);
    }
}
