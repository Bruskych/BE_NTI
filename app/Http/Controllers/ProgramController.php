<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Http\Resources\ProgramResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ProgramController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Program::class, 'program');
    }

    public function index(): JsonResponse
    {
        $programs = Program::active()->get();

        return response()->api(ProgramResource::collection($programs));
    }

    public function show(Program $program): JsonResponse
    {
        return response()->api(new ProgramResource($program));
    }
}
