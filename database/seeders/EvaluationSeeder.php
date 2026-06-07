<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;

use App\Models\User;
use App\Models\Evaluation;
use App\Models\Application;
use App\Models\EvaluationScore;
use App\Models\EvaluationCriteria;

class EvaluationSeeder extends Seeder
{
    /**
     * Оценки от экспертов
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        EvaluationScore::truncate();
        Evaluation::truncate();
        Schema::enableForeignKeyConstraints();

        // ------------------------------
        // Ручное создание
        // ------------------------------

        $criteria = EvaluationCriteria::all();
        if ($criteria->isEmpty()) {
            return;
        }

        if (Application::count() === 0) {
            $this->call(ApplicationSeeder::class);
        }

        $evaluator = User::where('email', 'evaluator@nti.sk')->first();
        $firstApplication = Application::first();

        if ($evaluator) {
            $staticEvaluation = Evaluation::create([
                'application_id' => $firstApplication->id,
                'evaluator_id'   => $evaluator->id,
                'comment'        => 'First official evaluation with static predefined metrics.',
                'recommendation' => 'approve',
                'total_score'    => '0.00',
            ]);
            $staticTotal = 0.0;
            foreach ($criteria as $criterion) {
                $score = 4.0;
                $weight = (float) str_replace(',', '.', $criterion->weight);
                $staticTotal += $score * $weight;
                EvaluationScore::create([
                    'evaluation_id' => $staticEvaluation->id,
                    'criteria_id'   => $criterion->id,
                    'score'         => '4.0',
                    'comment'       => 'Static criteria grade.',
                ]);
            }
            $staticEvaluation->update(['total_score' => round($staticTotal, 2)]);
        }

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        for ($i = 0; $i < 5; $i++) {
            $randomApplication = Application::where('id', '!=', $firstApplication?->id ?? 0)
                ->inRandomOrder()
                ->first();

            if (!$randomApplication) {
                break;
            }

            $fakeEvaluator = User::factory()->create();
            $fakeEvaluator->assignRole('evaluator');

            $autoEvaluation = Evaluation::factory()->create([
                'application_id' => $randomApplication->id,
                'evaluator_id'   => $fakeEvaluator->id,
            ]);

            $autoTotal = 0.0;
            foreach ($criteria as $criterion) {
                $rawScore = fake()->randomElement([3.0, 4.0, 4.5, 5.0]);
                $weight = (float) str_replace(',', '.', $criterion->weight);
                $autoTotal += $rawScore * $weight;

                EvaluationScore::factory()->create([
                    'evaluation_id' => $autoEvaluation->id,
                    'criteria_id'   => $criterion->id,
                    'score'         => $rawScore,
                ]);
            }
            $autoEvaluation->update(['total_score' => round($autoTotal, 2)]);
        }
    }
}
