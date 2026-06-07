<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\ExportsLog;
use App\Models\User;

/**
 * @extends Factory<ExportsLog>
 */
class ExportsLogFactory extends Factory
{
    /**
     * Логи экспорта
     *
     * @return array<string, mixed>
     */
    protected $model = ExportsLog::class;
    public function definition(): array
    {
        return [
            'user_id'       => fn() => User::inRandomOrder()->first()?->id ?? User::factory(),
            'export_type'   => fake()->randomElement(
                [
                    'users_csv',
                    'projects_xlsx',
                    'applications_pdf'
                ]
            ),
            'filters_json'  => ['status' => fake()->randomElement(
                [
                    'active',
                    'pending'
                ]
            ), 'date_from'  => '2026-01-01'],
            'file_path'     => 'exports/' . fake()->uuid() . '.' . fake()->randomElement(
                [
                    'csv',
                    'xlsx',
                    'pdf'
                ]
            ),
            'created_at'    => fake()->dateTimeBetween('-2 months', 'now'),
        ];
    }
}
