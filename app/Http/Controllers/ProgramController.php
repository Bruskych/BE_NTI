<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\FormFieldResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/** Контроллер программ: список программ и поля формы заявки */
class ProgramController extends Controller
{

    public function __construct()
    {
        $this->authorizeResource(Program::class, 'program');
    }

    /** Возвращает список активных программ */
    #[OA\Get(
        path: '/programs',
        summary: 'List active programs',
        tags: ['Programs'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'List of active programs'),
        ]
    )]
    public function index(): JsonResponse
    {
        $programs = Program::active()->get();

        return $this->apiJson(ProgramResource::collection($programs));
    }

    /** Возвращает детали одной программы */
    #[OA\Get(
        path: '/programs/{id}',
        summary: 'Get a single program',
        tags: ['Programs'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Program detail'),
            new OA\Response(response: 404, description: 'Program not found'),
        ]
    )]
    public function show(Program $program): JsonResponse
    {
        return $this->apiJson(new ProgramResource($program));
    }

    /** Возвращает поля формы заявки для программы и опционально для конкретного конкурсного отбора */
    #[OA\Get(
        path: '/programs/{program}/form-fields',
        summary: 'Get the application form fields for a program (and optionally a specific call)',
        tags: ['Programs'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'program', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'call_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'List of form fields'),
        ]
    )]
    public function formFields(Request $request, Program $program): JsonResponse
    {
        $callId = $request->integer('call_id') ?: null;

        $fields = $program->formFields()
            ->where(function ($query) use ($callId) {
                $query->whereNull('call_id');

                if ($callId) {
                    $query->orWhere('call_id', $callId);
                }
            })
            ->orderBy('order')
            ->get();

        return $this->apiJson(FormFieldResource::collection($fields));
    }
}
