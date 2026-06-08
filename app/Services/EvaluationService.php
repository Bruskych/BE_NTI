<?php

namespace App\Services;

use App\Models\{Application, Evaluation, EvaluationCriteria, EvaluationScore};
use Illuminate\Support\Facades\DB;

class EvaluationService
{
    public function storeEvaluation(Application $application, int $evaluatorId, array $data): Evaluation
    {
        return DB::transaction(function () use ($application, $evaluatorId, $data) {
            $totalScore = 0;

            $evaluation = Evaluation::create([
                'application_id' => $application->id,
                'evaluator_id'   => $evaluatorId,
                'total_score'    => 0, // Обновим позже
                'comment'        => $data['comment'] ?? null,
                'recommendation' => $data['recommendation'],
            ]);

            foreach ($data['scores'] as $scoreData) {
                $criteria = EvaluationCriteria::findOrFail($scoreData['criteria_id']);

                EvaluationScore::create([
                    'evaluation_id' => $evaluation->id,
                    'criteria_id'   => $criteria->id,
                    'score'         => $scoreData['score'],
                    'comment'       => $scoreData['comment'] ?? null,
                ]);

                $totalScore += ($scoreData['score'] * $criteria->weight);
            }

            $evaluation->update(['total_score' => $totalScore]);

            return $evaluation;
        });
    }
}
