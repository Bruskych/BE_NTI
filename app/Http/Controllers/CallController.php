<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Http\Resources\CallResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class CallController extends Controller
{
    use AuthorizesRequests;

    /**
     * Получить список всех вызовов с их связями.
     */
    public function index(): JsonResponse
    {
        $calls = Call::with(['program', 'specializations'])->latest()->get();

        return response()->json(CallResource::collection($calls));
    }

    /**
     * Посмотреть детальную информацию о вызове.
     */
    public function show(int $id): JsonResponse
    {
        $call = Call::with(['program', 'specializations', 'evaluationTemplate'])->findOrFail($id);

        return response()->json(new CallResource($call));
    }

    /**
     * Создать новый вызов (Доступно только Admin).
     */
    public function store(Request $request): JsonResponse
    {
        $this->authorize('create', Call::class);

        $validated = $request->validate([
            'program_id'             => 'required|exists:programs,id',
            'title'                  => 'required|string|max:255',
            'description'            => 'nullable|string',
            'deadline'               => 'required|date|after:now',
            'status'                 => 'required|in:draft,open,closed',
            'budget'                 => 'nullable|numeric|min:0',
            'evaluation_template_id' => 'nullable|exists:evaluation_templates,id',
            'specialization_ids'     => 'required|array',
            'specialization_ids.*'   => 'exists:specializations,id',
        ]);

        $call = Call::create($validated);

        $call->specializations()->sync($validated['specialization_ids']);

        return response()->json(
            new CallResource($call->load(['program', 'specializations'])),
            201
        );
    }

    /**
     * Обновить существующий вызов (Доступно только Admin).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $call = Call::findOrFail($id);

        $this->authorize('update', $call);

        $validated = $request->validate([
            'program_id'             => 'sometimes|required|exists:programs,id',
            'title'                  => 'sometimes|required|string|max:255',
            'description'            => 'nullable|string',
            'deadline'               => 'sometimes|required|date',
            'status'                 => 'sometimes|required|in:draft,open,closed',
            'budget'                 => 'nullable|numeric|min:0',
            'evaluation_template_id' => 'nullable|exists:evaluation_templates,id',
            'specialization_ids'     => 'sometimes|required|array',
            'specialization_ids.*'   => 'exists:specializations,id',
        ]);

        $call->update($validated);

        if (isset($validated['specialization_ids'])) {
            $call->specializations()->sync($validated['specialization_ids']);
        }

        return response()->json(new CallResource($call->load(['program', 'specializations'])));
    }
}
