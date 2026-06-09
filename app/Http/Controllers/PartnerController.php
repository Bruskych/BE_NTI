<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Http\Resources\PartnerResource;
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/** Контроллер партнёров: публичный каталог партнёров и менторов платформы */
class PartnerController extends Controller
{
    /** Возвращает список партнёров, отсортированных по признаку избранного */
    #[OA\Get(
        path: '/partners',
        summary: 'List partners and mentors (public directory), featured first',
        tags: ['Partners'],
        responses: [
            new OA\Response(response: 200, description: 'List of partners'),
        ]
    )]
    public function index(): JsonResponse
    {
        $partners = Partner::with('organization')
            ->orderByDesc('is_featured')
            ->get();

        return $this->apiJson(PartnerResource::collection($partners));
    }

    /** Возвращает детали одного партнёра с данными об организации */
    #[OA\Get(
        path: '/partners/{partner}',
        summary: 'Get a single partner with organization details',
        tags: ['Partners'],
        parameters: [
            new OA\Parameter(name: 'partner', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Partner detail'),
            new OA\Response(response: 404, description: 'Partner not found'),
        ]
    )]
    public function show(Partner $partner): JsonResponse
    {
        return $this->apiJson(new PartnerResource($partner->load('organization')));
    }
}
