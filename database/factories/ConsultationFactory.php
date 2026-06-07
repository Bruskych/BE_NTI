<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

use App\Models\Consultation;
use App\Models\Mentorship;
use App\Models\Milestone;
use App\Models\User;

/**
 * @extends Factory<Consultation>
 */
class ConsultationFactory extends Factory
{
    /**
     * Консультации ментора
     *
     * @return array<string, mixed>
     */
    protected $model = Consultation::class;
    public function definition(): array {
        return [
            'mentorship_id'     => fn() => Mentorship::inRandomOrder()->first()?->id ?? Mentorship::factory(),
            'mentor_id'         => fn() => User::inRandomOrder()->first()?->id ?? User::factory(),
            'milestone_id'      => fn() => Milestone::inRandomOrder()->first()?->id ?? Milestone::factory(),
            'scheduled_at'      => now()->subDays(5),
            'completed_at'      => null,
            'notes'             => null,
            'recommendations'   => null,
        ];
    }

    public function completed(): static {
        return $this->state(function (array $attributes) {
            $scheduled = isset($attributes['scheduled_at']) ? Carbon::parse($attributes['scheduled_at']) : now()->subDays(5);
            return [
                'scheduled_at'      => $scheduled,
                'completed_at'      => $scheduled->copy()->addHours(2),
                'notes'             => 'We reviewed the current progress of the sprint. '.fake()->sentence(5),
                'recommendations'   => 'It is recommended to correct the comments on the code and cover it with tests. '.fake()->sentence(5),
            ];
        });
    }

    public function scheduled(): static {
        return $this->state(fn() => [
            'scheduled_at'      => now()->addDays(rand(1, 15)),
            'completed_at'      => null,
            'notes'             => null,
            'recommendations'   => null,
        ]);
    }
}
