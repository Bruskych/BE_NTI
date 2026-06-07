<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Call;
use App\Models\Program;
use App\Models\EvaluationTemplate;

/**
 * @extends Factory<Call>
 */
class CallFactory extends Factory
{
    /**
     * Окно для сборка заявок
     *
     * @return array<string, mixed>
     */
    protected $model = Call::class;
    public function definition(): array {
        return [
            'program_id'             => fn() => Program::inRandomOrder()->first()?->id,
            'title'                  => fake()->randomElement(
                [
                    'Autumn Research Grant',
                    'Digital Innovation Subvention',
                    'Green Tech Accelerator',
                    'University Venture Fund'
                ]
            ),
            'description'            => fake()->realText(250),
            'deadline'               => fake()->dateTimeBetween('+1 month', '+3 months'),
            'status'                 => fake()->randomElement(
                [
                    'draft',
                    'open',
                    'closed'
                ]
            ),
            'budget'                 => fake()->randomElement(
                [
                    '25000.00',
                    '50000.00',
                    '100000.00',
                    '150000.00'
                ]
            ),
            'evaluation_template_id' => EvaluationTemplate::factory(),
        ];
    }
}
