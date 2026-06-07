<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Application;
use App\Models\Program;
use App\Models\Team;

/**
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    /**
     * Контейнер заявки: основная анкета, сквозной статус и итоговый балл
     *
     * @return array<string, mixed>
     */
    protected $model = Application::class;
    public function definition(): array {
        return [
            'program_id'        => fn() => Program::inRandomOrder()->first()?->id,
            'team_id'           => fn() => Team::inRandomOrder()->first()?->id,
            'status'            => fake()->randomElement(
                [
                    Application::STATUS_DRAFT,
                    Application::STATUS_SUBMITTED,
                    Application::STATUS_VERIFIED,
                    Application::STATUS_IN_EVALUATION,
                    Application::STATUS_APPROVED,
                    Application::STATUS_REJECTED,
                    Application::STATUS_ACTIVE
                ]
            ),
            'submitted_at'      => fake()->dateTimeBetween('-1 month', 'now'),
            'total_score'       => fake()->randomFloat(2, 60, 100),
            'decision_comment'  => fake()->boolean(50) ? fake()->sentence() : null,
        ];
    }
}
