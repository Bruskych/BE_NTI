<?php

namespace App\Http\Controllers;

use App\Models\Specialization;
use App\Http\Resources\SpecializationResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SpecializationController extends Controller
{
    /**
     * Получить публичный список всех специализаций и квалификационных стеков.
     */
    public function index(): JsonResponse
    {
        $specializations = Specialization::all();
        return response()->json(SpecializationResource::collection($specializations));
    }
}
