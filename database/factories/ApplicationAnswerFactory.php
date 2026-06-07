<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\ApplicationAnswer;
use App\Models\FormField;

/**
 * @extends Factory<ApplicationAnswer>
 */
class ApplicationAnswerFactory extends Factory
{
    /**
     * Динамические ответы: значения полей конструктора анкеты (текст, файлы, JSON-сметы)
     *
     * @return array<string, mixed>
     */
    protected $model = ApplicationAnswer::class;
    public function definition(): array {
        return [
            'field_id'      => fn() => FormField::inRandomOrder()->first()?->id,
            'value_text'    => fake()->sentence(),
            'value_json'    => null,
            'file_path'     => null,
        ];
    }
}
