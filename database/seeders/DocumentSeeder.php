<?php
namespace Database\Seeders;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

use App\Models\Application;
use App\Models\Document;
use App\Models\Project;
use App\Models\User;

/**
 * Сидер документов: создаёт тестовые документы (контракты, отчёты, паспорта) для проектов и заявок.
 * Зависит от ProjectSeeder и MilestoneSeeder.
 */
class DocumentSeeder extends Seeder
{
    /**
     * Документы
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        DB::table('milestone_documents')->truncate();
        Document::truncate();
        Schema::enableForeignKeyConstraints();

        // ------------------------------
        // Ручное создание
        // ------------------------------

        $user = User::first();
        $staticProject = Project::where('title', 'Version control system for Blockbench models')->first();

        if ($staticProject) {
            $staticDoc = Document::create([
                'application_id'    => $staticProject->application_id,
                'project_id'        => $staticProject->id,
                'milestone_id'      => null,
                'type'              => 'specification',
                'file_name'         => 'blockbench_architecture_v1.pdf',
                'file_path'         => 'documents/static_spec.pdf',
                'mime_type'         => 'application/pdf',
                'size'              => 2048576,
                'version'           => 1,
                'classification'    => Document::CLASSIFICATION_INTERNAL,
                'uploaded_by'       => $user?->id,
            ]);

            $staticMilestone = $staticProject->milestones()->first();
            if ($staticMilestone) {
                $milestoneDoc = Document::create([
                    'application_id'    => $staticProject->application_id,
                    'project_id'        => $staticProject->id,
                    'milestone_id'      => $staticMilestone->id,
                    'type'              => 'report',
                    'file_name'         => 'json_schema_report.docx',
                    'file_path'         => 'documents/static_report.docx',
                    'mime_type'         => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                    'size'              => 512000,
                    'version'           => 1,
                    'classification'    => Document::CLASSIFICATION_INTERNAL,
                    'uploaded_by'       => $user?->id,
                ]);
                $staticMilestone->documents()->attach($milestoneDoc->id);
            }
        }

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        $applications = Application::inRandomOrder()->take(5)->get();
        foreach ($applications as $app) {
            Document::factory()->identityProof()->create([
                'application_id' => $app->id,
            ]);
        }

        $projects = Project::with('milestones')->get();
        foreach ($projects as $project) {
            if ($staticProject && $project->id === $staticProject->id) {
                continue;
            }

            Document::factory()->contract()->create([
                'application_id' => $project->application_id,
                'project_id'     => $project->id,
            ]);

            foreach ($project->milestones as $milestone) {
                if (fake()->boolean(70)) {
                    $doc = Document::factory()->report()->create([
                        'application_id' => $project->application_id,
                        'project_id'     => $project->id,
                        'milestone_id'   => $milestone->id,
                    ]);
                    $milestone->documents()->attach($doc->id);
                }
            }
        }
    }
}
