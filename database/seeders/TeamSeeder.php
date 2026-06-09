<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Seeder;

use App\Models\Specialization;
use App\Models\Team;
use App\Models\User;

/**
 * Сидер студенческих команд: создаёт команды с лидерами, участниками и специализациями.
 * Зависит от UserSeeder и SpecializationSeeder.
 */
class TeamSeeder extends Seeder
{
    /**
     * Команды студентов
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        Team::truncate();
        DB::table('team_specialization')->truncate();
        DB::table('team_user')->truncate();
        Schema::enableForeignKeyConstraints();

        // ------------------------------
        // Ручное создание
        // ------------------------------

        $users = User::all();
        $specializations = Specialization::all();
        if ($users->isEmpty()) {
            return;
        }
        $leader = $users->first();
        $staticTeam = Team::create([
            'name'          => 'Dream Team NTI',
            'leader_id'     => $leader->id,
            'description'   => 'A core team of developers building services using Vue 3 and Laravel.',
            'skills_json'   => [
                'Vue 3',
                'Laravel',
                'Docker',
                'Git'
            ],
            'capacity'      => 5,
            'status'        => 'formed',
        ]);
        $staticTeam->users()->attach($leader->id, ['role' => 'leader', 'joined_at' => now()]);
        if ($users->count() > 1) {
            $members = $users->skip(1)->take(3)->pluck('id')->toArray();
            foreach ($members as $memberId) {
                $staticTeam->users()->attach($memberId, ['role' => 'member', 'joined_at' => now()]);
            }
        }
        if ($specializations->isNotEmpty()) {
            $staticTeam->specializations()->attach($specializations->pluck('id')->toArray());
        }

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        $teams = Team::factory()->count(8)->create();
        foreach ($teams as $team) {
            if ($team->leader_id) {
                $team->users()->attach($team->leader_id, ['role' => 'leader', 'joined_at' => now()]);
            }
            $potentialMembers = $users->where('id', '!=', $team->leader_id);
            if ($potentialMembers->isNotEmpty()) {
                $memberCount = rand(1, min($team->capacity - 1, $potentialMembers->count()));
                $randomMembers = $potentialMembers->random($memberCount)->pluck('id')->toArray();
                foreach ($randomMembers as $userId) {
                    $team->users()->attach($userId, ['role' => 'member', 'joined_at' => now()]);
                }
            }
            if ($specializations->isNotEmpty()) {
                $team->specializations()->attach(
                    $specializations->random(rand(1, min(2, $specializations->count())))->pluck('id')->toArray()
                );
            }
        }
    }
}
