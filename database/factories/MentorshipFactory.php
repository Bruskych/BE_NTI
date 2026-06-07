<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Carbon;

use App\Models\Mentorship;
use App\Models\Project;
use App\Models\User;

/**
 * @extends Factory<Mentorship>
 */
class MentorshipFactory extends Factory
{
    /**
     * Ментор проекта
     *
     * @return array<string, mixed>
     */
    protected $model = Mentorship::class;
    public function definition(): array {
        return [
            'project_id'    => fn() => Project::inRandomOrder()->first()?->id ?? Project::factory(),
            'mentor_id'     => fn() => User::inRandomOrder()->first()?->id ?? User::factory(),
            'status'        => 'active',
            'started_at'    => now()->subMonths(1),
            'finished_at'   => null,
        ];
    }

    public function finished($startedAt): static {
        return $this->state(function () use ($startedAt) {
            $start = Carbon::parse($startedAt);
            return [
                'status'        => 'finished',
                'finished_at'   => $start->copy()->addDays(30),
            ];
        });
    }
}
