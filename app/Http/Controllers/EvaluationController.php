<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Evaluation; // Нужно для Evaluation::class в authorize()
use App\Http\Resources\EvaluationResource;
use App\Http\Requests\StoreEvaluationRequest;
use App\Services\EvaluationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EvaluationController extends Controller
{
    use AuthorizesRequests;

    public function store(StoreEvaluationRequest $request, Application $application, EvaluationService $service): JsonResponse
    {
        $this->authorize('create', [Evaluation::class, $application]);

        $evaluation = $service->storeEvaluation(
            $application,
            $request->user()->id,
            $request->validated()
        );

        return response()->api(new EvaluationResource($evaluation), 201);
    }
}
