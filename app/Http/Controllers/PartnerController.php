<?php

namespace App\Http\Controllers;

use App\Models\Partner;
use App\Http\Resources\PartnerResource;
use Illuminate\Http\JsonResponse;

class PartnerController extends Controller
{
    /**
     * Spec 9: public "Partners and mentors" directory — list of partners with references.
     */
    public function index(): JsonResponse
    {
        $partners = Partner::with('organization')
            ->orderByDesc('is_featured')
            ->get();

        return response()->api(PartnerResource::collection($partners));
    }

    public function show(Partner $partner): JsonResponse
    {
        return response()->api(new PartnerResource($partner->load('organization')));
    }
}
