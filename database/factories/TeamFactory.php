<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Team;
use App\Models\User;

/**
 * @extends Factory<Team>
 */
class TeamFactory extends Factory
{
    /**
     * Команды студентов
     *
     * @return array<string, mixed>
     */
    protected $model = Team::class;
    public function definition(): array {
        return [
            'name'          => fake()->unique()->company() . ' Squad',
            'leader_id'     => fn() => User::inRandomOrder()->first()?->id,
            'description'   => fake()->realText(200),
            'skills_json'   => fake()->randomElements(
                [
                    'Vue 3',
                    'Laravel',
                    'Tailwind CSS',
                    'Docker',
                    'MySQL',
                    'TypeScript',
                    'REST API'
                ], rand(2, 4)
            ),
            'capacity'      => fake()->numberBetween(3, 5),
            'status'        => fake()->randomElement(
                [
                    'recruiting',
                    'formed',
                    'active'
                ]
            ),
        ];
    }
}
