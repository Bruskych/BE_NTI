<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Http\Resources\ProgramResource;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ProgramController extends Controller
{
    /**
     * Получить список всех активных программ (Program A и Program B).
     */
    public function index(): JsonResponse
    {
        $programs = Program::where('is_active', true)->get();
        return response()->json(ProgramResource::collection($programs));
    }

    /**
     * Посмотреть детальную информацию о конкретной программе.
     */
    public function show(int $id): JsonResponse
    {
        $program = Program::where('is_active', true)->findOrFail($id);
        return response()->json(new ProgramResource($program));
    }
}
