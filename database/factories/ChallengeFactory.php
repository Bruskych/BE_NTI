<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Organization;
use App\Models\Challenge;
use App\Models\Program;
use App\Models\User;

/**
 * @extends Factory<Challenge>
 */
class ChallengeFactory extends Factory
{
    /**
     * Задачи / Челленджи
     *
     * @return array<string, mixed>
     */
    protected $model = Challenge::class;
    public function definition(): array {
        return [
            'program_id'                => fn() => Program::inRandomOrder()->first()?->id,
            'organization_id'           => fn() => Organization::inRandomOrder()->first()?->id,
            'title'                     => fake()->randomElement(
                [
                    'ERP development for logistics',
                    'Mobile app for sharing',
                    'AI document analysis module',
                    'Integration of IoT sensors into an eco-monitoring system'
                ]
            ),
            'description'               => fake()->realText(300),
            'technical_specification'   => fake()->realText(1000),
            'budget'                    => fake()->randomElement(
                [
                    150000.00,
                    300000.00,
                    450000.00,
                    600000.00
                ]
            ),
            'product_owner_id'          => fn() => User::inRandomOrder()->first()?->id,
            'deadline'                  => fake()->dateTimeBetween('+2 months', '+5 months'),
            'status'                    => fake()->randomElement(
                [
                    'draft',
                    'published',
                    'pairing',
                    'assigned',
                    'active',
                    'closed'
                ]
            ),
            'max_applications'          => fake()->numberBetween(2, 5),
            'backlog_order'             => fake()->numberBetween(0, 10),
        ];
    }
}
