<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\EmailTemplate;

/**
 * @extends Factory<EmailTemplate>
 */
class EmailTemplateFactory extends Factory
{
    /**
     * Шаблоны писем
     *
     * @return array<string, mixed>
     */
    protected $model = EmailTemplate::class;
    public function definition(): array
    {
        return [
            'name'              => fake()->unique()->word(),
            'subject'           => fake()->sentence(5),
            'body'              => 'Good afternoon, {{ name }}. We would like to inform you that the status of your project has been changed to {{ status }}.',
            'variables_json'    => ['name', 'status'],
        ];
    }
}
