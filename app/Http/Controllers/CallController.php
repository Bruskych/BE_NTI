<?php

namespace App\Http\Controllers;

use App\Models\Call;
use App\Http\Resources\CallResource;
use App\Http\Requests\StoreCallRequest;
use App\Http\Requests\UpdateCallRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Routing\Controllers\HasMiddleware;
use OpenApi\Attributes as OA;

/** Контроллер конкурсных отборов (calls): CRUD и управление статусом */
class CallController extends Controller implements HasMiddleware
{
    use AuthorizesRequests;

    public static function middleware(): array
    {
        return static::resourcePolicyMiddleware(Call::class, 'call');
    }

    /** Возвращает список всех конкурсных отборов с программой и специализациями */
    #[OA\Get(
        path: '/calls',
        summary: 'List calls with program and specializations',
        tags: ['Calls'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'List of calls'),
        ]
    )]
    public function index(): JsonResponse
    {
        $calls = Call::with(['program', 'specializations'])->latest()->get();
        return $this->apiJson(CallResource::collection($calls));
    }

    /** Возвращает список открытых конкурсных отборов (публично, без аутентификации) — для главной страницы */
    #[OA\Get(
        path: '/calls/open',
        summary: 'List currently open calls with deadlines (public, no authentication required)',
        tags: ['Calls'],
        responses: [
            new OA\Response(response: 200, description: 'List of open calls'),
        ]
    )]
    public function openCalls(): JsonResponse
    {
        $calls = Call::with(['program', 'specializations'])
            ->where('status', Call::STATUS_OPEN)
            ->orderBy('deadline')
            ->get();

        return $this->apiJson(CallResource::collection($calls));
    }

    /** Возвращает детали одного конкурсного отбора */
    #[OA\Get(
        path: '/calls/{call}',
        summary: 'Get a single call with program, specializations and evaluation template',
        tags: ['Calls'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'call', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Call detail'),
            new OA\Response(response: 403, description: 'Not authorized to view this call'),
            new OA\Response(response: 404, description: 'Call not found'),
        ]
    )]
    public function show(Call $call): JsonResponse
    {
        $call->load(['program', 'specializations', 'evaluationTemplate']);
        return $this->apiJson(new CallResource($call));
    }

    /** Создаёт конкурсный отбор, при необходимости связывая специализации */
    #[OA\Post(
        path: '/calls',
        summary: 'Create a call, optionally linking specializations',
        tags: ['Calls'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Call created'),
            new OA\Response(response: 403, description: 'Not authorized to create calls'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreCallRequest $request): JsonResponse
    {
        $call = DB::transaction(function () use ($request) {
            $call = Call::create($request->validated());
            if ($request->has('specialization_ids')) {
                $call->specializations()->sync($request->specialization_ids);
            }
            return $call;
        });

        return $this->apiJson(new CallResource($call->load(['program', 'specializations'])), 201);
    }

    /** Обновляет конкурсный отбор и при необходимости синхронизирует специализации */
    #[OA\Put(
        path: '/calls/{call}',
        summary: 'Update a call, optionally re-syncing specializations',
        tags: ['Calls'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'call', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Call updated'),
            new OA\Response(response: 403, description: 'Not authorized to update this call'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateCallRequest $request, Call $call): JsonResponse
    {
        $call = DB::transaction(function () use ($request, $call) {
            $call->update($request->validated());
            if ($request->has('specialization_ids')) {
                $call->specializations()->sync($request->specialization_ids);
            }
            return $call;
        });

        return $this->apiJson(new CallResource($call->load(['program', 'specializations'])));
    }

    /** Удаляет черновой конкурсный отбор */
    #[OA\Delete(
        path: '/calls/{call}',
        summary: 'Delete a draft call (only the owning organization may delete its own draft calls)',
        tags: ['Calls'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'call', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Call deleted'),
            new OA\Response(response: 403, description: 'Not authorized to delete this call'),
        ]
    )]
    public function destroy(Call $call): JsonResponse
    {
        $call->delete();
        return $this->apiJson(['message' => 'Call deleted successfully.']);
    }

    /** Открывает конкурсный отбор для приёма заявок */
    #[OA\Post(
        path: '/calls/{call}/open',
        summary: '[Admin] Open a call for applications',
        tags: ['Calls'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'call', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Call opened'),
            new OA\Response(response: 403, description: 'Not authorized to open this call'),
        ]
    )]
    public function open(Call $call): JsonResponse
    {
        $this->authorize('open', $call);
        $call->update(['status' => 'open']);

        return $this->apiJson(new CallResource($call));
    }

    /** Закрывает конкурсный отбор */
    #[OA\Post(
        path: '/calls/{call}/close',
        summary: '[Admin] Close a call for applications',
        tags: ['Calls'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'call', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Call closed'),
            new OA\Response(response: 403, description: 'Not authorized to close this call'),
        ]
    )]
    public function close(Call $call): JsonResponse
    {
        $this->authorize('close', $call);
        $call->update(['status' => 'closed']);

        return $this->apiJson(new CallResource($call));
    }
}
