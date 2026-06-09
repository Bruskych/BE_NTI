<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

use App\Models\EvaluationTemplate;
use App\Models\EvaluationCriteria;
use App\Models\Program;

/**
 * Сидер шаблонов оценивания: создаёт шаблоны и критерии для экспертной оценки заявок.
 * Зависит от ProgramSeeder — шаблон привязывается к программе.
 */
class EvaluationTemplateSeeder extends Seeder
{
    /**
     * Шаблон оценки
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        EvaluationCriteria::truncate();
        EvaluationTemplate::truncate();
        Schema::enableForeignKeyConstraints();

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        if (Program::count() === 0) {
            $this->call(ProgramSeeder::class);
        }
        $program = Program::first();

        for ($i = 0; $i < 5; $i++) {
            $template = EvaluationTemplate::factory()->create([
                'program_id' => $program->id,
            ]);

            // ------------------------------
            // Ручное создание
            // ------------------------------

            EvaluationCriteria::factory()->create([
                'template_id' => $template->id,
                'name'        => 'Technical Implementation',
                'description' => 'Code quality, database architecture, and framework utilization.',
                'weight'      => '0.40',
                'order'       => 1,
            ]);
            EvaluationCriteria::factory()->create([
                'template_id' => $template->id,
                'name'        => 'Feasibility & Scalability',
                'description' => 'How realistic, secure, and scalable the solution is for business.',
                'weight'      => '0.30',
                'order'       => 2,
            ]);
            EvaluationCriteria::factory()->create([
                'template_id' => $template->id,
                'name'        => 'Presentation & Demo',
                'description' => 'Quality of the pitch, UI/UX responsiveness, and live demonstration.',
                'weight'      => '0.30',
                'order'       => 3,
            ]);
        }
    }
}
