<?php

namespace Database\Factories;

use App\Models\ApplicationPairingSubmission;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ApplicationPairingSubmission>
 */
class ApplicationPairingSubmissionFactory extends Factory
{
    /**
     * Мэтчинг (Программа Б): целевые файлы отбора команд под задачи бизнеса (CV, мотивационные письма)
     *
     * @return array<string, mixed>
     */
    protected $model = ApplicationPairingSubmission::class;
    public function definition(): array {
        return [
            'type'          => fake()->randomElement(
                [
                    'cv',
                    'motivation_letter',
                    'solution_proposal'
                ]
            ),
            'file_path'     => 'uploads/pairing/' . fake()->uuid() . '.pdf',
            'notes'         => fake()->boolean(50) ? fake()->sentence() : null,
        ];
    }
}
