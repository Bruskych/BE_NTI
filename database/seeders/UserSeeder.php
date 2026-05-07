<?php

namespace Database\Seeders;

use App\Models\Organization;
use App\Models\StudentProfile;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Super admin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@nti.sk'],
            ['name' => 'Super Admin', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $superAdmin->assignRole('super_admin');

        // 2. Admin
        $admin = User::firstOrCreate(
            ['email' => 'admin@nti.sk'],
            ['name' => 'NTI Admin', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $admin->assignRole('admin');

        // 3. Content editor
        $editor = User::firstOrCreate(
            ['email' => 'editor@nti.sk'],
            ['name' => 'Content Editor', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $editor->assignRole('content_editor');

        // 4. Evaluator
        $evaluator = User::firstOrCreate(
            ['email' => 'evaluator@nti.sk'],
            ['name' => 'Evaluator Komisie', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $evaluator->assignRole('evaluator');

        // 5. Mentor
        $mentor = User::firstOrCreate(
            ['email' => 'mentor@nti.sk'],
            ['name' => 'Mentor Novák', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $mentor->assignRole('mentor');

        // 6. Team leader
        $teamLeader = User::firstOrCreate(
            ['email' => 'leader@student.nti.sk'],
            ['name' => 'Vedúci Tímu', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $teamLeader->assignRole('team_leader');
        StudentProfile::firstOrCreate(
            ['user_id' => $teamLeader->id],
            [
                'study_program'        => 'Informatika',
                'year'                 => 3,
                'avg_grade'            => 1.8,
                'has_carried_subjects' => false,
                'skills_json'          => json_encode(['PHP', 'Laravel', 'Vue.js']),
            ]
        );

        // 7. Student
        $student = User::firstOrCreate(
            ['email' => 'student@student.nti.sk'],
            ['name' => 'Ján Študent', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $student->assignRole('student');
        StudentProfile::firstOrCreate(
            ['user_id' => $student->id],
            [
                'study_program'        => 'Aplikovaná informatika',
                'year'                 => 2,
                'avg_grade'            => 2.1,
                'has_carried_subjects' => false,
                'skills_json'          => json_encode(['Python', 'SQL']),
            ]
        );

        // 8. Company user
        $companyUser = User::firstOrCreate(
            ['email' => 'company@firma.sk'],
            ['name' => 'Peter Firemný', 'password' => Hash::make('password'), 'email_verified_at' => now()]
        );
        $companyUser->assignRole('company');

        $organization = Organization::firstOrCreate(
            ['tax_id' => 'SK123456789'],
            [
                'name'         => 'TestFirma s.r.o.',
                'sector'       => 'IT & Technológie',
                'website_link' => 'https://testfirma.sk',
                'description'  => 'Testovacia firma pre NTI Program B.',
                'status'       => 'active',
            ]
        );

        $organization->users()->syncWithoutDetaching([
            $companyUser->id => ['role' => 'owner'],
        ]);
    }
}
