<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\EvaluationCriteria;
use App\Models\EvaluationTemplate;

/**
 * @extends Factory<EvaluationCriteria>
 */
class EvaluationCriteriaFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = EvaluationCriteria::class;
    public function definition(): array {
        return [
            'template_id' => EvaluationTemplate::factory(),
            'name'        => fake()->word(),
            'description' => fake()->sentence(),
            'weight'      => '0.25',
            'order'       => fake()->numberBetween(1, 10),
        ];
    }
}
