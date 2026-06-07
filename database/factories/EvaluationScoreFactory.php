<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\EvaluationScore;
use App\Models\EvaluationCriteria;
use App\Models\Evaluation;

/**
 * @extends Factory<EvaluationScore>
 */
class EvaluationScoreFactory extends Factory
{
    /**
     * Баллы по критериям
     *
     * @return array<string, mixed>
     */
    protected $model = EvaluationScore::class;
    public function definition(): array {
        return [
            'evaluation_id' => Evaluation::factory(),
            'criteria_id'   => EvaluationCriteria::factory(),
            'score'         => fake()->randomElement(
                [
                    '3.5',
                    '4.0',
                    '4.5',
                    '5.0'
                ]
            ),
            'comment'       => fake()->sentence(),
        ];
    }
}
