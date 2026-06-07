<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\NotificationPreference;
use App\Models\User;

/**
 * @extends Factory<NotificationPreference>
 */
class NotificationPreferenceFactory extends Factory
{
    /**
     * Настройки уведомлений
     *
     * @return array<string, mixed>
     */
    protected $model = NotificationPreference::class;
    public function definition(): array
    {
        return [
            'user_id'                   => fn() => User::inRandomOrder()->first()?->id ?? User::factory(),
            'email_enabled'             => fake()->boolean(90),
            'system_enabled'            => true,
            'marketing_enabled'         => fake()->boolean(20),
            'deadline_alerts_enabled'   => fake()->boolean(85),
        ];
    }
}
