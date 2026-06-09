<?php

namespace App\Services;

use App\Models\{Application, AuditEvent, Evaluation, EvaluationCriteria, EvaluationScore};
use Illuminate\Support\Facades\DB;

/** Сервис сохранения оценки заявки: вычисляет взвешенный итоговый балл и фиксирует аудит-событие */
class EvaluationService
{
    /** Создаёт оценку с баллами по критериям, вычисляет итоговый балл и логирует аудит */
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

            AuditEvent::create([
                'user_id'         => $evaluatorId,
                'action'          => 'evaluation_submitted',
                'object_type'     => 'evaluation',
                'object_id'       => $evaluation->id,
                'old_values_json' => [],
                'new_values_json' => [
                    'application_id' => $application->id,
                    'recommendation' => $evaluation->recommendation,
                    'total_score'    => $totalScore,
                ],
                'result'          => 'success',
                'created_at'      => now(),
            ]);

            return $evaluation->load('scores.criteria', 'evaluator');
        });
    }
}
