<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

use App\Models\Document;
use App\Models\User;

/**
 * @extends Factory<Document>
 */
class DocumentFactory extends Factory
{
    /**
     * Документы
     *
     * @return array<string, mixed>
     */
    protected $model = Document::class;
    private array $mimeTypes = [
        'pdf'  => 'application/pdf',
        'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
        'jpg'  => 'image/jpeg',
        'png'  => 'image/png'
    ];

    public function definition(): array {
        $ext = fake()->randomElement(
            [
                'pdf',
                'docx',
                'xlsx',
                'pptx',
                'jpg',
                'png'
            ]
        );
        return [
            'application_id' => null,
            'project_id'     => null,
            'milestone_id'   => null,
            'type'           => 'specification',
            'file_name'      => fake()->words(2, true) . '.' . $ext,
            'file_path'      => 'documents/' . fake()->uuid() . '.' . $ext,
            'mime_type'      => $this->mimeTypes[$ext],
            'size'           => fake()->numberBetween(51200, 5242880),
            'version'        => 1,
            'classification' => Document::CLASSIFICATION_INTERNAL,
            'uploaded_by'    => fn() => User::inRandomOrder()->first()?->id,
        ];
    }

    /**
     * Состояние: Удостоверение личности (для заявок)
     */
    public function identityProof(): static
    {
        return $this->state(function () {
            $ext = fake()->randomElement(['jpg', 'png', 'pdf']);
            return [
                'type'           => 'identity_proof',
                'file_name'      => 'passport_' . fake()->word() . '.' . $ext,
                'mime_type'      => $this->mimeTypes[$ext],
                'classification' => Document::CLASSIFICATION_CONFIDENTIAL,
            ];
        });
    }

    /**
     * Состояние: Договор на проект
     */
    public function contract(): static
    {
        return $this->state(fn() => [
            'type'           => 'contract',
            'file_name'      => 'contract_' . fake()->numberBetween(100, 999) . '.pdf',
            'mime_type'      => $this->mimeTypes['pdf'],
            'classification' => Document::CLASSIFICATION_CONFIDENTIAL,
        ]);
    }

    /**
     * Состояние: Отчет по этапу разработки
     */
    public function report(): static
    {
        return $this->state(function () {
            $ext = fake()->randomElement(['docx', 'xlsx', 'pdf']);
            return [
                'type'           => 'report',
                'file_name'      => 'milestone_report_' . fake()->word() . '.' . $ext,
                'mime_type'      => $this->mimeTypes[$ext],
                'classification' => Document::CLASSIFICATION_INTERNAL,
            ];
        });
    }
}
