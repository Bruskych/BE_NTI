<?php

namespace App\Http\Controllers;

use App\Models\Specialization;
use App\Http\Resources\SpecializationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class SpecializationController extends Controller
{
    use AuthorizesRequests;

    public function __construct()
    {
        $this->authorizeResource(Specialization::class, 'specialization');
    }

    public function index(): JsonResponse
    {
        $specializations = Specialization::all();
        return response()->api(SpecializationResource::collection($specializations));
    }

    public function show(Specialization $specialization): JsonResponse
    {
        return response()->api(new SpecializationResource($specialization));
    }
}
