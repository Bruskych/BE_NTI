<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;

use App\Models\Application;
use App\Models\Project;

/**
 * Сидер проектов: создаёт проекты для одобренных заявок (статус approved/active).
 * Зависит от ApplicationSeeder — должен выполняться строго после него.
 */
class ProjectSeeder extends Seeder
{
    /**
     * Проекты под заявку
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Project::truncate();
        Schema::enableForeignKeyConstraints();

        // ------------------------------
        // Ручное создание
        // ------------------------------

        $staticApp = Application::where('status', Application::STATUS_ACTIVE)->whereNotNull('challenge_id')->first();
        if ($staticApp) {
            Project::create([
                'application_id'    => $staticApp->id,
                'title'             => 'Version control system for Blockbench models',
                'description'       => 'A static project for testing change tracking functionality in 3D models.',
                'status'            => 'active',
                'started_at'        => now()->subDays(5),
                'finished_at'       => null,
                'final_score'       => null,
            ]);
        }

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        $availableApps = Application::whereIn('status',
            [Application::STATUS_APPROVED, Application::STATUS_ACTIVE]
        )->doesntHave('project')->get();

        foreach ($availableApps as $app) {
            Project::factory()->create([
                'application_id' => $app->id,
            ]);
        }
    }
}
