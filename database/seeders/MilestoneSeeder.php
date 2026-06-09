<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;

use App\Models\Milestone;
use App\Models\Project;
use App\Models\User;

/**
 * Сидер этапов разработки: создаёт контрольные точки (milestones) для каждого проекта.
 * Зависит от ProjectSeeder — должен выполняться после него.
 */
class MilestoneSeeder extends Seeder
{
    /**
     * Контрольные этапы разработки проекта
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Milestone::truncate();
        Schema::enableForeignKeyConstraints();

        // ------------------------------
        // Ручное создание
        // ------------------------------

        $staticProject = Project::where('status', 'active')->first();
        $user = User::first();
        if ($staticProject) {
            Milestone::create([
                'project_id'                => $staticProject->id,
                'title'                     => 'Designing a Database Schema and JSON Structure',
                'description'               => 'It is necessary to describe in detail the entities for tracking the history of changes to blockbench files.',
                'deadline'                  => now()->addDays(14),
                'status'                    => 'completed',
                'completion_percentage'     => 100,
                'completed_at'              => now()->subDays(2),
                'approved_by'               => $user?->id,
            ]);
            Milestone::create([
                'project_id'                => $staticProject->id,
                'title'                     => 'Developing a model commit parser',
                'description'               => 'Implement backend functionality for comparing two binary versions of files.',
                'deadline'                  => now()->addDays(35),
                'status'                    => 'pending',
                'completion_percentage'     => 40,
                'completed_at'              => null,
                'approved_by'               => null,
            ]);
        }

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        $projects = Project::where('id', '!=', $staticProject?->id)->get();
        $user = User::first();

        foreach ($projects as $project) {
            $stagesCount = rand(3, 5);
            for ($i = 1; $i <= $stagesCount; $i++) {
                $isLast = ($i === $stagesCount);
                $factory = Milestone::factory()->for($project);
                if ($project->status === 'finished' || (!$isLast && fake()->boolean(70))) {
                    $factory->completed(stageNumber: $i, userId: $user?->id)->create();
                } else {
                    $factory->pending(stageNumber: $i)->create();
                }
            }
        }
    }
}
