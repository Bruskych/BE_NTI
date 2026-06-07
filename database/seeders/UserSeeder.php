<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

use App\Models\Organization;
use App\Models\User;

class UserSeeder extends Seeder
{
    /**
     * Email: superadmin@nti.sk             - role: super_admin
     * Email: admin@nti.sk                  - role: admin
     * Email: editor@nti.sk                 - role: content_editor
     * Email: evaluator@nti.sk              - role: evaluator
     * Email: company@firma.sk              - role: company
     * Email: mentor@nti.sk                 - role: mentor
     * Email: leader@student.nti.sk         - role: team_leader
     * Email: student@student.nti.sk        - role: student
     * Email: visitor@nti.sk                - role: visitor
     */
    public function run(): void
    {
        Schema::disableForeignKeyConstraints();
        User::truncate();
        Schema::enableForeignKeyConstraints();

        // ------------------------------
        // Ручное создание
        // ------------------------------

        $superAdmin = User::create([
            'email'             => 'superadmin@nti.sk',
            'name'              => 'Super Admin',
            'password'          => Hash::make('password'),
            'email_verified_at' => now()
        ]);
        $superAdmin->assignRole('super_admin');

        $admin = User::create([
            'email'             => 'admin@nti.sk',
            'name'              => 'NTI Admin',
            'password'          => Hash::make('password'),
            'email_verified_at' => now()
        ]);
        $admin->assignRole('admin');

        $editor = User::create([
            'email'             => 'editor@nti.sk',
            'name'              => 'Content Editor',
            'password'          => Hash::make('password'),
            'email_verified_at' => now()
        ]);
        $editor->assignRole('content_editor');

        $evaluator = User::create([
            'email'             => 'evaluator@nti.sk',
            'name'              => 'Evaluator Komisie',
            'password'          => Hash::make('password'),
            'email_verified_at' => now()
        ]);
        $evaluator->assignRole('evaluator');

        $mentor = User::create([
            'email'             => 'mentor@nti.sk',
            'name'              => 'Mentor Novák',
            'password'          => Hash::make('password'),
            'email_verified_at' => now()
        ]);
        $mentor->assignRole('mentor');

        $teamLeader = User::create([
            'email'             => 'leader@student.nti.sk',
            'name'              => 'Vedúci Tímu',
            'password'          => Hash::make('password'),
            'email_verified_at' => now()
        ]);
        $teamLeader->assignRole('team_leader');

        $companyUser = User::create([
            'email'             => 'company@firma.sk',
            'name'              => 'Peter Firemný',
            'password'          => Hash::make('password'),
            'email_verified_at' => now()
        ]);
        $companyUser->assignRole('company');

        $student = User::create([
            'email'             => 'student@student.nti.sk',
            'name'              => 'Ján Študent',
            'password'          => Hash::make('password'),
            'email_verified_at' => now()
        ]);
        $student->assignRole('student');

        $visitor = User::create([
            'email'             => 'visitor@nti.sk',
            'name'              => 'Samuel Visitor',
            'password'          => Hash::make('password'),
            'email_verified_at' => now()
        ]);
        $visitor->assignRole('visitor');

        // ------------------------------
        // Автосоздание с помощью фабрики
        // ------------------------------

        User::factory()->count(5)->student()->create();
    }
}
