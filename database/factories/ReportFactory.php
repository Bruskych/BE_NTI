<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Report;
use App\Models\User;

/**
 * @extends Factory<Report>
 */
class ReportFactory extends Factory
{
    /**
     * Отчёты
     *
     * @return array<string, mixed>
     */
    protected $model = Report::class;
    public function definition(): array
    {
        return [
            'name'              => fake()->sentence(3),
            'type'              => fake()->randomElement(
                [
                    'quarterly_performance',
                    'student_activity_metrics',
                    'program_expenditure'
                ]
            ),
            'parameters_json'   => ['year' => 2026, 'include_archived' => fake()->boolean()],
            'generated_by'      => fn() => User::whereHas('roles', fn($q) => $q->whereIn('name', ['admin', 'super_admin']))->first()?->id ?? User::factory(),
            'file_path'         => fake()->optional(85)->passthrough('reports/' . fake()->uuid() . '.pdf'),
        ];
    }
}
