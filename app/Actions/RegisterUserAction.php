<?php

namespace App\Actions;

use App\Models\User;
use App\Models\Team;
use App\Models\Application;
use App\Models\Organization;
use App\Models\Notification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class RegisterUserAction
{
    public function execute(array $data): User
    {
        return DB::transaction(function () use ($data) {
            $user = User::create([
                'name'     => $data['name'],
                'email'    => $data['email'],
                'password' => Hash::make($data['password']),
            ]);

            $user->notificationPreference()->create([
                'email_enabled'           => true,
                'system_enabled'          => true,
                'marketing_enabled'       => false,
                'deadline_alerts_enabled' => true,
            ]);

            $user->assignRole('visitor');

            if ($data['role'] === 'student') {
                $user->studentProfile()->create();
                $this->createStudentTeam($user);
            }
            elseif ($data['role'] === 'company') {
                $this->createCompanyRegistration($user, $data);
            }

            return $user;
        });
    }

    private function createStudentTeam(User $user): void
    {
        $team = Team::create([
            'name'      => 'Team ' . $user->name,
            'leader_id' => $user->id,
            'status'    => 'active',
        ]);

        $team->members()->attach($user->id, ['role' => 'leader', 'joined_at' => now()]);

        Application::create([
            'program_id' => 1,
            'team_id'    => $team->id,
            'status'     => 'submitted',
            'submitted_at' => now(),
        ]);
    }

    private function createCompanyRegistration(User $user, array $data): void
    {
        $organization = Organization::create([
            'name'          => $data['company_name'],
            'tax_id'        => $data['company_tax_id'],
            'sector'        => $data['sector'],
            'website_link'  => $data['website_link'],
            'description'   => $data['description'],
            'status'        => 'inactive',
        ]);

        $user->organizations()->attach($organization->id, ['role' => 'owner']);

        $team = Team::create([
            'name'      => 'Team ' . $organization->name,
            'leader_id' => $user->id,
            'status'    => 'pending',
        ]);

        $team->members()->attach($user->id, ['role' => 'leader', 'joined_at' => now()]);

        Application::create([
            'program_id' => 1,
            'organization_id' => $organization->id,
            'team_id'    => $team->id,
            'status'     => 'submitted',
            'submitted_at' => now(),
        ]);

        Notification::create([
            'user_id'   => $user->id,
            'type'      => 'company_registration_submitted',
            'message'   => 'Your company registration request has been submitted.',
        ]);
    }
}
