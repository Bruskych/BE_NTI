<?php

namespace App\Http\Controllers;

use App\Models\{Consultation, Mentorship};
use App\Http\Resources\ConsultationResource;
use App\Http\Requests\{StoreConsultationRequest, UpdateConsultationRequest};
use Illuminate\Http\JsonResponse;
use OpenApi\Attributes as OA;

/** Контроллер консультаций: создание, просмотр и управление встречами ментора */
class ConsultationController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Consultation::class, 'consultation');
    }

    /** Возвращает список консультаций текущего пользователя или всех (при наличии права) */
    #[OA\Get(
        path: '/consultations',
        summary: 'List consultations (own as mentor/team member, all for holders of consultations.view)',
        tags: ['Consultations'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'List of consultations'),
        ]
    )]
    public function index(): JsonResponse
    {
        $user = request()->user();

        $query = Consultation::with(['mentor', 'milestone']);

        if (!$user->can('consultations.view')) {
            $query->where(function ($q) use ($user) {
                $q->where('mentor_id', $user->id)
                    ->orWhereHas(
                        'mentorship.project.application.team.members',
                        fn ($members) => $members->where('users.id', $user->id)
                    );
            });
        }

        return $this->apiJson(ConsultationResource::collection($query->get()));
    }

    /** Возвращает детали одной консультации */
    #[OA\Get(
        path: '/consultations/{consultation}',
        summary: 'Get a single consultation with mentor and milestone',
        tags: ['Consultations'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'consultation', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Consultation detail'),
            new OA\Response(response: 403, description: 'Not authorized to view this consultation'),
            new OA\Response(response: 404, description: 'Consultation not found'),
        ]
    )]
    public function show(Consultation $consultation): JsonResponse
    {
        return $this->apiJson(new ConsultationResource($consultation->load(['mentor', 'milestone'])));
    }

    /** Создаёт новую консультацию для ментора по одному из его менторств */
    #[OA\Post(
        path: '/consultations',
        summary: '[Mentor] Schedule a consultation for one of their mentorships',
        tags: ['Consultations'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Consultation created'),
            new OA\Response(response: 403, description: 'Not authorized to create consultations, or not the mentor of this mentorship'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreConsultationRequest $request): JsonResponse
    {
        $this->authorize('create', Consultation::class);

        $mentorship = Mentorship::findOrFail($request->mentorship_id);

        if ($mentorship->mentor_id !== $request->user()->id) {
            return $this->apiJson(['message' => 'You are not the mentor of this mentorship.'], 403);
        }

        $consultation = Consultation::create(array_merge($request->validated(), [
            'mentor_id' => $request->user()->id,
        ]));

        return $this->apiJson(new ConsultationResource($consultation), 201);
    }

    /** Обновляет данные консультации */
    #[OA\Put(
        path: '/consultations/{consultation}',
        summary: 'Update a consultation',
        tags: ['Consultations'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'consultation', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Consultation updated'),
            new OA\Response(response: 403, description: 'Not authorized to update this consultation'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateConsultationRequest $request, Consultation $consultation): JsonResponse
    {
        $consultation->update($request->validated());

        return $this->apiJson(new ConsultationResource($consultation->load(['mentor', 'milestone'])));
    }

    /** Удаляет консультацию */
    #[OA\Delete(
        path: '/consultations/{consultation}',
        summary: 'Delete a consultation',
        tags: ['Consultations'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'consultation', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Consultation deleted'),
            new OA\Response(response: 403, description: 'Not authorized to delete this consultation'),
        ]
    )]
    public function destroy(Consultation $consultation): JsonResponse
    {
        $consultation->delete();

        return $this->apiJson(['message' => 'Consultation deleted successfully.']);
    }
}
