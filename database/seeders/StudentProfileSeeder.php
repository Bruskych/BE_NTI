<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

use App\Models\User;
use App\Models\StudentProfile;

class StudentProfileSeeder extends Seeder
{
    /**
     * Создание профилей для конкретных ролей
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        StudentProfile::truncate();
        Schema::enableForeignKeyConstraints();

        // ------------------------------
        // Ручное создание
        // ------------------------------

        $teamLeader = User::where('email', 'leader@student.nti.sk')->first();
        if ($teamLeader) {
            StudentProfile::create(
                [
                    'user_id'              => $teamLeader->id,
                    'study_program'        => 'Informatics',
                    'year'                 => 3,
                    'avg_grade'            => 1.8,
                    'has_carried_subjects' => false,
                    'skills_json'          => json_encode(['PHP', 'Laravel', 'Vue.js']),
                ]
            );
        }

        $student = User::where('email', 'student@student.nti.sk')->first();
        if ($student) {
            StudentProfile::create(
                [
                    'user_id'              => $student->id,
                    'study_program'        => 'Applied Informatics',
                    'year'                 => 2,
                    'avg_grade'            => 2.1,
                    'has_carried_subjects' => false,
                    'skills_json'          => json_encode(['Python', 'SQL']),
                ]
            );
        }

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        for ($i = 0; $i < 5; $i++) {
            $newStudent = User::factory()->create();
            $newStudent->assignRole('student');
            StudentProfile::factory()->create([
                'user_id' => $newStudent->id,
            ]);
        }
    }
}
