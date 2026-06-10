<?php

namespace App\Http\Controllers;

use App\Models\Specialization;
use App\Http\Resources\SpecializationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use OpenApi\Attributes as OA;

/** Контроллер специализаций: список и просмотр направлений подготовки */
class SpecializationController extends Controller implements HasMiddleware
{
    public static function middleware(): array
    {
        return static::resourcePolicyMiddleware(Specialization::class, 'specialization');
    }

    /** Возвращает список специализаций; фильтр ?stack=01…05 для квалификационных стеков */
    #[OA\Get(
        path: '/specializations',
        summary: 'List all specializations (optionally filter by qualification stack)',
        tags: ['Specializations'],
        parameters: [
            new OA\Parameter(name: 'stack', in: 'query', required: false, description: 'Filter by qualification stack (01–05)', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of specializations'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Specialization::query();

        if ($request->filled('stack')) {
            $query->where('stack', $request->input('stack'));
        }

        return $this->apiJson(SpecializationResource::collection($query->get()));
    }

    /** Возвращает одну специализацию по идентификатору */
    public function show(Specialization $specialization): JsonResponse
    {
        return $this->apiJson(new SpecializationResource($specialization));
    }
}
