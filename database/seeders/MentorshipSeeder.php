<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Seeder;

use App\Models\Consultation;
use App\Models\Mentorship;
use App\Models\Project;
use App\Models\User;

class MentorshipSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Consultation::truncate();
        Mentorship::truncate();
        Schema::enableForeignKeyConstraints();

        // ------------------------------
        // Ручное создание
        // ------------------------------

        $staticProject = Project::where('title', 'Version control system for Blockbench models')->first() ?? Project::first();
        $mentor = User::where('id', '!=', $staticProject?->application?->team?->leader_id)->first() ?? User::first();

        if ($staticProject && $mentor) {
            $staticMentorship = Mentorship::create([
                'project_id'  => $staticProject->id,
                'mentor_id'   => $mentor->id,
                'status'      => 'active',
                'started_at'  => now()->subDays(6),
                'finished_at' => null,
            ]);

            $milestone = $staticProject->milestones()->first();
            if ($milestone) {
                Consultation::create([
                    'mentorship_id'   => $staticMentorship->id,
                    'mentor_id'       => $mentor->id,
                    'milestone_id'    => $milestone->id,
                    'scheduled_at'    => now()->subDays(3),
                    'completed_at'    => now()->subDays(3),
                    'notes'           => 'We analyzed the architecture of the blockbench models JSON tree.',
                    'recommendations' => 'It is recommended to optimize the rendering of revision history on the frontend using Vue 3.',
                ]);
            }
        }

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        $projects = Project::where('id', '!=', $staticProject?->id)->get();

        foreach ($projects as $project) {
            $projectMentor = User::inRandomOrder()->first();
            if (!$projectMentor) {
                continue;
            }
            $mentorshipFactory = Mentorship::factory();
            if ($project->status === 'finished') {
                $startedAt = fake()->dateTimeBetween('-5 months', '-2 months');
                $mentorship = $mentorshipFactory->finished($startedAt)->create([
                    'project_id' => $project->id,
                    'mentor_id'  => $projectMentor->id,
                    'started_at' => $startedAt,
                ]);
            } else {
                $mentorship = $mentorshipFactory->create([
                    'project_id' => $project->id,
                    'mentor_id'  => $projectMentor->id,
                ]);
            }

            $milestones = $project->milestones;
            if ($milestones->isNotEmpty()) {
                $consultationsCount = rand(1, 3);

                for ($i = 0; $i < $consultationsCount; $i++) {
                    $consultationFactory = Consultation::factory();

                    if ($mentorship->status === 'finished') {
                        $consultationFactory->completed()->create([
                            'mentorship_id' => $mentorship->id,
                            'mentor_id'     => $projectMentor->id,
                            'milestone_id'  => $milestones->random()->id,
                        ]);
                    } else {
                        $state = fake()->boolean(75) ? 'completed' : 'scheduled';
                        $consultationFactory->$state()->create([
                            'mentorship_id' => $mentorship->id,
                            'mentor_id'     => $projectMentor->id,
                            'milestone_id'  => $milestones->random()->id,
                        ]);
                    }
                }
            }
        }
    }
}
