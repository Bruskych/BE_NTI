<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Evaluation;
use App\Models\EvaluationScore;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EvaluationController extends Controller
{
    use AuthorizesRequests;
    public function store(Request $request, Application $application)
    {
        $this->authorize('create', $application);

        $validated = $request->validate([
            'comment'        => 'nullable|string',
            'recommendation' => 'required|in:approve,reject',
            'scores'         => 'required|array|min:1',
            'scores.*.criteria_id' => 'required|exists:evaluation_criteria,id',
            'scores.*.score'       => 'required|numeric|min:0|max:10',
            'scores.*.comment'     => 'nullable|string',
        ]);

        $evaluation = DB::transaction(function () use ($validated, $application, $request) {
            $evaluation = Evaluation::create([
                'application_id' => $application->id,
                'evaluator_id'   => $request->user()->id,
                'total_score'    => 0,
                'comment'        => $validated['comment'],
                'recommendation' => $validated['recommendation'],
            ]);

            $total = 0;
            foreach ($validated['scores'] as $scoreData) {
                EvaluationScore::create([
                    'evaluation_id' => $evaluation->id,
                    'criteria_id'   => $scoreData['criteria_id'],
                    'score'         => $scoreData['score'],
                    'comment'       => $scoreData['comment'] ?? null,
                ]);
                $total += $scoreData['score'];
            }

            $evaluation->update(['total_score' => $total]);

            return $evaluation;
        });

        return response()->json(['message' => 'Evaluation saved', 'data' => $evaluation], 201);
    }
}
