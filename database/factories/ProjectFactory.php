<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Application;
use App\Models\Project;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /**
     * Генерация данных для проектов
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
        $startedAd = fake()->optional()->dateTimeBetween('-2 years', 'now');
        $finishedAd = fake()->boolean(75);
        return [
            'application_id'    => Application::factory(),
            'title'             => fake()->sentence(3),
            'description'       => fake()->paragraph(),
            'status'            => $finishedAd ? 'finished' : 'active',
            'started_at'        => $startedAd,
            'finished_at'       => $finishedAd && $startedAd ? fake()->dateTimeBetween($startedAd, 'now') : null,
            'final_score'       => $finishedAd ? fake()->randomFloat(2,50,100) : null,
        ];
    }

    public function active(): static {
        return $this->state(fn() =>[
            'status'        => 'active',
            'finished_at'   => null,
            'final_score'   => null,
        ]);
    }

    public function finished(): static {
        return $this->state(function() {
            $startedAd = fake()->dateTimeBetween('-2 years', '-1 months');
            return [
                'status'        => 'finished',
                'started_at'    => $startedAd,
                'finished_at'   => fake()->dateTimeBetween($startedAd, 'now'),
                'final_score'   => fake()->randomFloat(2,65,100),
            ];
        });
    }
}
