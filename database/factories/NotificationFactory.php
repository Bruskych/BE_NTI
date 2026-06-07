<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Notification;
use App\Models\User;

/**
 * @extends Factory<Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Уведомления
     *
     * @return array<string, mixed>
     */
    protected $model = Notification::class;
    public function definition(): array
    {
        $channel = fake()->randomElement(
            [
                Notification::CHANNEL_SYSTEM,
                Notification::CHANNEL_EMAIL,
                Notification::CHANNEL_PUSH
            ]
        );
        $isRead = fake()->boolean(40);
        return [
            'user_id'       => fn() => User::inRandomOrder()->first()?->id ?? User::factory(),
            'type'          => fake()->randomElement(
                [
                    'project_status',
                    'milestone_reminder',
                    'consultation_scheduled'
                ]
            ),
            'channel'       => $channel,
            'title'         => fake()->sentence(4),
            'message'       => fake()->paragraph(),
            'data_json'     => ['action_url' => '/dashboard/projects/' . fake()->randomNumber(2)],
            'read_at'       => $isRead ? fake()->dateTimeBetween('-1 week', 'now') : null,
        ];
    }
}
