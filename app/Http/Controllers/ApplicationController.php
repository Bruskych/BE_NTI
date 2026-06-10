<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Http\Resources\ApplicationResource;
use App\Http\Requests\{StoreApplicationRequest, UpdateApplicationRequest, DecideApplicationRequest};
use App\Services\ApplicationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/** Контроллер заявок: создание, просмотр, обновление, подача и принятие решений */
class ApplicationController extends Controller
{
    public function __construct()
    {
        $this->authorizeResource(Application::class, 'application');
    }

    /** Возвращает постраничный список заявок */
    #[OA\Get(
        path: '/applications',
        summary: 'List applications (paginated)',
        tags: ['Applications'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of applications'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user->hasRole(['admin', 'super_admin']) || $user->can('applications.view-all')) {
            $apps = Application::with(['program', 'call', 'team'])->paginate();
        } else {
            $teamIds = $user->teams()->pluck('teams.id');
            $apps = Application::with(['program', 'call', 'team'])
                ->whereIn('team_id', $teamIds)
                ->get();
        }

        return $this->apiJson(ApplicationResource::collection($apps));
    }

    /** Возвращает детали одной заявки со связанными данными */
    #[OA\Get(
        path: '/applications/{application}',
        summary: 'Get a single application with team, organization, answers and pairing submissions',
        tags: ['Applications'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'application', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Application detail'),
            new OA\Response(response: 403, description: 'Not authorized to view this application'),
            new OA\Response(response: 404, description: 'Application not found'),
        ]
    )]
    public function show(Application $application): JsonResponse
    {
        return $this->apiJson(new ApplicationResource($application->load([
            'team', 'organization', 'answers.field', 'pairingSubmissions',
            'call.evaluationTemplate.criteria',
        ])));
    }

    /** Создаёт черновик заявки для текущей команды */
    #[OA\Post(
        path: '/applications',
        summary: 'Create a draft application for the current team and an open call',
        tags: ['Applications'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Application created (draft)'),
            new OA\Response(response: 403, description: 'Not authorized to create applications'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function store(StoreApplicationRequest $request, ApplicationService $service): JsonResponse
    {
        $this->authorize('create', Application::class);
        $team = $request->user()->teams()->where('leader_id', $request->user()->id)->first();

        $application = $service->createApplication($request->validated(), $team->id, $request->user()->id);

        return $this->apiJson(new ApplicationResource($application), 201);
    }

    /** Обновляет ответы, документы для парного отбора или организацию заявки */
    #[OA\Put(
        path: '/applications/{application}',
        summary: 'Update an application\'s answers, pairing submissions or organization',
        tags: ['Applications'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'application', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Application updated'),
            new OA\Response(response: 403, description: 'Not authorized to update this application'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function update(UpdateApplicationRequest $request, Application $application, ApplicationService $service): JsonResponse
    {
        $validated = $request->validated();

        $application->update(\Illuminate\Support\Arr::only($validated, ['organization_id']));

        if (!empty($validated['answers'])) {
            $service->saveAnswers($application, $validated['answers']);
        }

        if (!empty($validated['pairing_submissions'])) {
            $service->savePairingSubmissions($application, $validated['pairing_submissions']);
        }

        return $this->apiJson(new ApplicationResource(
            $application->fresh(['team', 'organization', 'answers.field', 'pairingSubmissions'])
        ));
    }

    /** Подаёт черновик заявки на рассмотрение комиссией */
    #[OA\Post(
        path: '/applications/{application}/submit',
        summary: 'Submit a draft application for committee review',
        tags: ['Applications'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'application', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Application submitted'),
            new OA\Response(response: 403, description: 'Not authorized to submit this application'),
        ]
    )]
    public function submit(Request $request, Application $application, ApplicationService $service): JsonResponse
    {
        $this->authorize('submit', $application);
        $service->submitApplication($application, $request->user()->id);

        event(new \App\Events\ApplicationSubmitted($application));

        return $this->apiJson(['message' => 'Application submitted successfully']);
    }

    /** Фиксирует решение комиссии по поданной заявке */
    #[OA\Post(
        path: '/applications/{application}/decide',
        summary: '[Committee] Approve, reject or request supplements for a submitted application',
        tags: ['Applications'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'application', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Decision recorded, application updated'),
            new OA\Response(response: 403, description: 'Not authorized to decide on this application'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
    public function decide(DecideApplicationRequest $request, Application $application, ApplicationService $service): JsonResponse
    {
        $this->authorize('decide', $application);
        $decision = $request->validated('decision');

        $application = $service->decideApplication(
            $application,
            $request->validated('decision'),
            $request->validated('comment'),
            $request->user()->id
        );

        event(new \App\Events\ApplicationDecided($application, $decision));

        return $this->apiJson(new ApplicationResource($application));
    }

    /** Удаляет заявку */
    #[OA\Delete(
        path: '/applications/{application}',
        summary: 'Delete an application',
        tags: ['Applications'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'application', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Application deleted'),
            new OA\Response(response: 403, description: 'Not authorized to delete this application'),
        ]
    )]
    public function destroy(Application $application): JsonResponse
    {
        $application->delete();
        return $this->apiJson(null, 204);
    }
}
