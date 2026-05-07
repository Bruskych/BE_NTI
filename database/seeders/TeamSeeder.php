<?php

namespace Database\Seeders;

use App\Models\Specialization;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class TeamSeeder extends Seeder
{
    public function run(): void
    {
        $leader  = User::where('email', 'leader@student.nti.sk')->first();
        $student = User::where('email', 'student@student.nti.sk')->first();

        // ---------------------------------------------------------
        // Tím 1 — Program A
        // ---------------------------------------------------------
        $team1 = Team::firstOrCreate(
            ['name' => 'InnoTech Team'],
            [
                'leader_id'   => $leader->id,
                'description' => 'Tím zameraný na vývoj inovatívnych softvérových riešení pre Program A.',
                'skills_json' => json_encode(['PHP', 'Laravel', 'Vue.js', 'MySQL']),
                'capacity'    => 5,
                'status'      => 'active',
            ]
        );

        // Pridaj členov
        $team1->members()->syncWithoutDetaching([
            $leader->id  => ['role' => 'leader', 'joined_at' => now()],
            $student->id => ['role' => 'member', 'joined_at' => now()],
        ]);

        // Pridaj špecializácie
        $specs1 = Specialization::whereIn('slug', [
            'webove-aplikacie',
            'stack-03-web',
        ])->pluck('id');
        $team1->specializations()->syncWithoutDetaching($specs1);

        // ---------------------------------------------------------
        // Tím 2 — Program B
        // ---------------------------------------------------------
        $team2 = Team::firstOrCreate(
            ['name' => 'AI Builders'],
            [
                'leader_id'   => $leader->id,
                'description' => 'Tím špecializovaný na AI a dátové technológie pre Program B.',
                'skills_json' => json_encode(['Python', 'TensorFlow', 'FastAPI', 'PostgreSQL']),
                'capacity'    => 4,
                'status'      => 'active',
            ]
        );

        $team2->members()->syncWithoutDetaching([
            $leader->id  => ['role' => 'leader', 'joined_at' => now()],
            $student->id => ['role' => 'member', 'joined_at' => now()],
        ]);

        $specs2 = Specialization::whereIn('slug', [
            'ai-datove-technologie',
            'stack-02-ai-data',
        ])->pluck('id');
        $team2->specializations()->syncWithoutDetaching($specs2);
    }
}
