<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Http\Resources\ApplicationResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class ApplicationController extends Controller
{
    use AuthorizesRequests;
    /**
     * Создание новой заявки (Draft)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'team_id'         => 'required|exists:teams,id',
            'program_id'      => 'required|exists:programs,id',
            'challenge_id'    => 'nullable|exists:challenges,id',
        ]);

        $team = \App\Models\Team::findOrFail($validated['team_id']);
        if ($team->leader_id !== $request->user()->id) {
            return response()->json(['message' => 'Only team leader can create application'], 403);
        }

        $application = Application::create([
            ...$validated,
            'status' => Application::STATUS_DRAFT,
        ]);

        return response()->json(new ApplicationResource($application), 201);
    }

    /**
     * Подача заявки (Draft -> Submitted)
     */
    public function submit(Application $application)
    {
        $this->authorize('submit', $application);

        $application->update([
            'status'       => Application::STATUS_SUBMITTED,
            'submitted_at' => now(),
        ]);

        return response()->json(['message' => 'Application submitted successfully']);
    }

    /**
     * Обновление данных заявки
     */
    public function update(Request $request, Application $application)
    {
        $this->authorize('update', $application);

        $validated = $request->validate([
            'challenge_id' => 'sometimes|exists:challenges,id',
        ]);

        $application->update($validated);

        return response()->json(new ApplicationResource($application));
    }
}
