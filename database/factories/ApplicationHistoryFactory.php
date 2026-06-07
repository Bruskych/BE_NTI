<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\ApplicationHistory;
use App\Models\User;

/**
 * @extends Factory<ApplicationHistory>
 */
class ApplicationHistoryFactory extends Factory
{
    /**
     * История заявки: аудит изменения статусов и комментарии модераторов
     *
     * @return array<string, mixed>
     */
    protected $model = ApplicationHistory::class;
    public function definition(): array {
        return [
            'old_status'    => fake()->randomElement(
                [
                    'draft',
                    'submitted'
                ]
            ),
            'new_status'    => fake()->randomElement(
                [
                    'verified',
                    'in_evaluation'
                ]
            ),
            'changed_by'    => fn() => User::inRandomOrder()->first()?->id,
            'comment'       => fake()->sentence(),
            'created_at'    => now(),
        ];
    }
}
