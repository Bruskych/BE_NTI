<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\FormField;
use App\Models\Program;
use App\Models\Call;

/**
 * @extends Factory<FormField>
 */
class FormFieldFactory extends Factory
{
    /**
     * Конструктор динамических анкет для подачи заявок
     *
     * @return array<string, mixed>
     */
    protected $model = FormField::class;
    public function definition(): array {
        $type = fake()->randomElement(
            [
                'text',
                'textarea',
                'select',
                'file'
            ]
        );
        $options = $type === 'select' ? ['Junior', 'Middle', 'Senior'] : null;
        $rules = match($type) {
            'file'      => 'required|file|mimes:pdf,zip|max:10240',
            'text'      => 'nullable|string|max:255',
            'textarea'  => 'required|string|min:50',
            default     => 'required',
        };

        return [
            'program_id'        => fn() => Program::inRandomOrder()->first()?->id,
            'call_id'           => fake()->boolean(50) ? fn() => Call::inRandomOrder()->first()?->id : null,
            'name'              => fake()->unique()->word() . '_field',
            'label'             => fake()->sentence(3),
            'type'              => $type,
            'required'          => fake()->boolean(70),
            'options_json'      => $options,
            'validation_rules'  => $rules,
            'order'             => fake()->numberBetween(1, 10),
        ];
    }
}
