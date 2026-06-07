<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\BulkMessage;
use App\Models\User;

/**
 * @extends Factory<BulkMessage>
 */
class BulkMessageFactory extends Factory
{
    /**
     * Массовые рассылки
     *
     * @return array<string, mixed>
     */
    protected $model = BulkMessage::class;
    public function definition(): array
    {
        return [
            'sender_id'     => fn() => User::whereHas('roles', fn($q) => $q->where('name', 'admin'))->first()?->id ?? User::factory(),
            'target_group'  => fake()->randomElement(
                [
                    'all',
                    'students',
                    'mentors'
                ]
            ),
            'subject'       => fake()->sentence(6),
            'body'          => fake()->paragraphs(3, true),
            'sent_at'       => fake()->optional(80)->dateTimeBetween('-1 month', 'now'),
        ];
    }
}
