<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

use App\Models\Specialization;
use App\Models\Organization;
use App\Models\Challenge;
use App\Models\Program;
use App\Models\User;

class ChallengeSeeder extends Seeder
{
    /**
     * Задачи / Челленджи
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Challenge::truncate();
        DB::table('challenge_specialization')->truncate();
        Schema::enableForeignKeyConstraints();

        // ------------------------------
        // Ручное создание
        // ------------------------------

        $practiceProgram = Program::where('type', 'practice')->first() ?? Program::first();
        $organization = Organization::first();
        $user = User::first();
        $specializations = Specialization::all();

        if ($practiceProgram && $organization) {
            $staticChallenge = Challenge::create([
                'program_id'                => $practiceProgram->id,
                'organization_id'           => $organization->id,
                'title'                     => 'Development of a version control system for 3D models',
                'description'               => 'It is necessary to create a web interface and backend to track changes in the blockbench extension binaries.',
                'technical_specification'   => 'Use Vue 3 on the frontend and Laravel on the backend. Ensure JSON structure parsing.',
                'budget'                    => 500000.00,
                'product_owner_id'          => $user ? $user->id : null,
                'deadline'                  => now()->addMonths(3),
                'status'                    => 'published',
                'max_applications'          => 3,
                'backlog_order'             => 1,
            ]);
            if ($specializations->isNotEmpty()) {
                $staticChallenge->specializations()->attach($specializations->pluck('id')->toArray());
            }
        }

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        $challenges = Challenge::factory()->count(10)->create();
        if ($specializations->isNotEmpty()) {
            foreach ($challenges as $challenge) {
                $challenge->specializations()->attach(
                    $specializations->random(rand(1, min(3, $specializations->count())))->pluck('id')->toArray()
                );
            }
        }
    }
}
