<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Evaluation; // Нужно для Evaluation::class в authorize()
use App\Http\Resources\EvaluationResource;
use App\Http\Requests\StoreEvaluationRequest;
use App\Services\EvaluationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use OpenApi\Attributes as OA;

/** Контроллер оценок: приём оценок от эксперта для поданных заявок */
class EvaluationController extends Controller
{
    use AuthorizesRequests;

    /** Сохраняет оценку эксперта по заявке с баллами по критериям и рекомендацией */
    #[OA\Post(
        path: '/applications/{application}/evaluations',
        summary: '[Evaluator] Submit a scored evaluation with recommendation for an application',
        tags: ['Evaluations'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'application', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 201, description: 'Evaluation recorded'),
            new OA\Response(response: 403, description: 'Not authorized to evaluate this application'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreEvaluationRequest $request, Application $application, EvaluationService $service): JsonResponse
    {
        $this->authorize('create', [Evaluation::class, $application]);

        $evaluation = $service->storeEvaluation(
            $application,
            $request->user()->id,
            $request->validated()
        );

        return $this->apiJson(new EvaluationResource($evaluation), 201);
    }
}
