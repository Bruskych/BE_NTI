<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;

/**
 * @extends Factory<Milestone>
 */
class MilestoneFactory extends Factory
{
    /**
     * Базовый скелет этапов работы
     *
     * @return array<string, mixed>
     */
    protected $model = Milestone::class;
    public function definition(): array {
        return [
            'project_id'            => fn() => Project::inRandomOrder()->first()?->id ?? Project::factory(),
            'title'                 => fake()->randomElement(
                [
                    'Requirements analysis',
                    'Architectural design',
                    'MVP development',
                    'System testing'
                ]
            ),
            'description'           => fake()->sentence(6),
            'deadline'              => fake()->dateTimeBetween('-1 month', '+3 months'),
            'status'                => 'pending',
            'completion_percentage' => fake()->numberBetween(0, 85),
            'completed_at'          => null,
            'approved_by'           => null,
        ];
    }

    public function completed(int $stageNumber, ?int $userId = null): static {
        return $this->state(fn() => [
            'title'                 => "Stage $stageNumber: " . fake()->randomElement(
                [
                    'Analysis of technical specifications',
                    'Interface prototype',
                    'Kernel development',
                    'API integration'
                ]
            ),
            'status'                => 'completed',
            'completion_percentage' => 100,
            'completed_at'          => now()->subDays(rand(1, 20)),
            'approved_by'           => $userId ?? User::first()?->id,
        ]);
    }

    public function pending(int $stageNumber): static {
        return $this->state(fn() => [
            'title'                 => "Stage $stageNumber: " . fake()->randomElement(
                [
                    'API integration',
                    'Acceptance',
                    'Final Demo Day'
                ]
            ),
            'status'                => 'pending',
            'completion_percentage' => fake()->numberBetween(15, 85),
            'completed_at'          => null,
            'approved_by'           => null,
        ]);
    }
}
