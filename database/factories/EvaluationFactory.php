<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Evaluation;
use App\Models\User;

/**
 * @extends Factory<Evaluation>
 */
class EvaluationFactory extends Factory
{
    /**
     * Оценки
     *
     * @return array<string, mixed>
     */
    protected $model = Evaluation::class;
    public function definition(): array {
        return [
            'application_id' => 1,
            'evaluator_id'   => User::whereHas('roles', fn($q) => $q->where('name', 'evaluator'))->first()?->id,
            'total_score'    => '0.00',
            'comment'        => fake()->realText(150),
            'recommendation' => fake()->randomElement(
                [
                    'approve',
                    'reject'
                ]
            ),
        ];
    }
}
