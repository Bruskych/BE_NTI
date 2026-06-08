<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Http\Resources\ProgramResource;
use App\Http\Resources\FormFieldResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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

    /**
     * Spec 6.3: configurable application form per program/call/applicant type.
     * Returns the program-wide fields plus, when a call is given, that call's specific fields.
     */
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

        return response()->api(FormFieldResource::collection($fields));
    }
}
