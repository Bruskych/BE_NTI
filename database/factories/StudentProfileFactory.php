<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\StudentProfile;
use App\Models\User;

/**
 * @extends Factory<StudentProfile>
 */
class StudentProfileFactory extends Factory
{
    /**
     * Профили студентов
     *
     * @return array<string, mixed>
     */
    protected $model = StudentProfile::class;
    public function definition(): array {
        return [
            'user_id'           => User::factory(),
            'study_program'     => fake()->randomElement(
                [
                    'Computer Science',
                    'Applied Informatics',
                    'Software Engineering',
                    'Data Science',
                    'Cybersecurity'
                ]
            ),
            'year'              => fake()->numberBetween(1, 4),
            'skills_json'       => fake()->randomElements(
                [
                    'PHP',
                    'Laravel',
                    'Vue.js',
                    'Python',
                    'SQL',
                    'JavaScript',
                    'TypeScript',
                    'Docker',
                    'C++',
                    'Java'
                ],
                fake()->numberBetween(2, 5)
            ),
            'cv_path'                       => 'cvs/' . fake()->uuid() . '.pdf',
            'avg_grade'                     => fake()->randomFloat(2, 1.0, 3.5),
            'has_carried_subjects'          => fake()->boolean(15),
            'eligibility_confirmed_at'      => fake()->boolean(80) ? fake()->dateTimeThisYear() : null,
            'eligibility_document_path'     => 'documents/eligibility/' . fake()->uuid() . '.pdf',
            'academic_documents_path'       => 'documents/academic/' . fake()->uuid() . '.pdf',
        ];
    }

    public function unconfirmed(): static {
        return $this->state(fn (array $attributes) => [
            'eligibility_confirmed_at' => null,
        ]);
    }

    public function withCarriedSubjects(): static {
        return $this->state(fn (array $attributes) => [
            'has_carried_subjects' => true,
        ]);
    }
}
