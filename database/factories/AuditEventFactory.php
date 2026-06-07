<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\AuditEvent;
use App\Models\User;

/**
 * @extends Factory<AuditEvent>
 */
class AuditEventFactory extends Factory
{
    /**
     * События аудита
     *
     * @return array<string, mixed>
     */
    protected $model = AuditEvent::class;
    public function definition(): array
    {
        $events = [
            'auth.login'            => [
                'type'  => 'App\Models\User',
                'old'   => null,
                'new'   => ['status' => 'online']
            ],
            'project.create'        => [
                'type'  => 'App\Models\Project',
                'old'   => null,
                'new'   => ['title' => fake()->sentence(3)]
            ],
            'application.submit'    => [
                'type'  => 'App\Models\Application',
                'old'   => ['status' => 'draft'],
                'new'   => ['status' => 'submitted']
            ],
        ];
        $action = fake()->randomKey($events);
        return [
            'user_id'           => fake()->boolean(85) ? (fn() => User::inRandomOrder()->first()?->id) : null,
            'action'            => $action,
            'object_type'       => $events[$action]['type'],
            'object_id'         => fake()->randomNumber(3),
            'old_values_json'   => $events[$action]['old'],
            'new_values_json'   => $events[$action]['new'],
            'ip_address'        => fake()->ipv4(),
            'user_agent'        => fake()->userAgent(),
            'result'            => fake()->randomElement(
                [
                    'success',
                    'success',
                    'failed'
                ]
            ),
            'created_at'        => fake()->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
