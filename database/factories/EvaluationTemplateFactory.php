<?php

namespace Database\Factories;

use App\Models\EvaluationTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\EvaluationCriteria;
use App\Models\Program;

/**
 * @extends Factory<EvaluationTemplate>
 */
class EvaluationTemplateFactory extends Factory
{
    /**
     * Шаблоны оценивания
     *
     * @return array<string, mixed>
     */
    protected $model = EvaluationTemplate::class;
    public function definition(): array {
        return [
            'program_id'  => fn() => Program::inRandomOrder()->first()?->id,
            'name'        => fake()->randomElement(
                [
                    'Technical Evaluation Matrix',
                    'Startup Pitch Assessment',
                    'Final Prototype Review',
                    'Milestone Grading Template'
                ]
            ),
            'description' => fake()->realText(200),
        ];
    }
}
