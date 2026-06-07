<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Роли и разрешения
     */
    public function run(): void
    {
        // ------------------------------
        // Ручное создание
        // ------------------------------

        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $permissions = [
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.assign-roles',
            'student-profiles.view', 'student-profiles.edit',
            'organizations.view', 'organizations.create', 'organizations.edit', 'organizations.delete',
            'programs.view', 'programs.create', 'programs.edit', 'programs.delete',
            'calls.view', 'calls.create', 'calls.edit', 'calls.delete', 'calls.open', 'calls.close',
            'challenges.view', 'challenges.view-all', 'challenges.create', 'challenges.edit', 'challenges.delete', 'challenges.publish', 'challenges.manage-backlog',
            'teams.view', 'teams.create', 'teams.edit', 'teams.delete', 'teams.invite-members',
            'applications.view-own', 'applications.view-all', 'applications.create', 'applications.edit', 'applications.submit', 'applications.change-status', 'applications.delete', 'applications.request-supplement',
            'evaluations.view', 'evaluations.create', 'evaluations.edit', 'evaluations.decide',
            'projects.view-own', 'projects.view-all', 'projects.edit',
            'mentorships.view', 'mentorships.assign', 'consultations.create', 'consultations.edit',
            'milestones.view', 'milestones.create', 'milestones.edit', 'milestones.approve',
            'documents.view', 'documents.upload', 'documents.delete',
            'notifications.send-bulk', 'notifications.manage-templates',
            'cms.posts.view', 'cms.posts.create', 'cms.posts.edit', 'cms.posts.delete',
            'cms.pages.view', 'cms.pages.create', 'cms.pages.edit', 'cms.pages.delete', 'cms.partners.manage',
            'reports.view', 'reports.create', 'exports.create',
            'audit.view', 'gdpr.manage',
            'system.settings', 'system.roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }
        Role::firstOrCreate(['name' => 'visitor', 'guard_name' => 'web']);

        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $student->syncPermissions([
            'student-profiles.view', 'student-profiles.edit',
            'teams.view', 'teams.create',
            'applications.view-own', 'applications.create', 'applications.edit', 'applications.submit',
            'projects.view-own', 'milestones.view', 'documents.view', 'documents.upload',
        ]);

        $teamLeader = Role::firstOrCreate(['name' => 'team_leader', 'guard_name' => 'web']);
        $teamLeader->syncPermissions([
            'student-profiles.view', 'student-profiles.edit',
            'teams.view', 'teams.create', 'teams.edit', 'teams.invite-members',
            'applications.view-own', 'applications.create', 'applications.edit', 'applications.submit',
            'projects.view-own', 'milestones.view', 'documents.view', 'documents.upload',
        ]);

        $company = Role::firstOrCreate(['name' => 'company', 'guard_name' => 'web']);
        $company->syncPermissions([
            'organizations.view', 'organizations.create', 'organizations.edit',
            'challenges.view', 'challenges.create', 'challenges.edit',
            'projects.view-own', 'milestones.view', 'documents.view', 'documents.upload',
        ]);

        $mentor = Role::firstOrCreate(['name' => 'mentor', 'guard_name' => 'web']);
        $mentor->syncPermissions([
            'projects.view-own', 'milestones.view', 'milestones.create', 'milestones.edit', 'milestones.approve',
            'consultations.create', 'consultations.edit',
            'mentorships.view', 'documents.view', 'documents.upload',
        ]);

        $evaluator = Role::firstOrCreate(['name' => 'evaluator', 'guard_name' => 'web']);
        $evaluator->syncPermissions([
            'applications.view-all', 'applications.request-supplement',
            'evaluations.view', 'evaluations.create', 'evaluations.edit', 'evaluations.decide',
            'documents.view',
        ]);

        $contentEditor = Role::firstOrCreate(['name' => 'content_editor', 'guard_name' => 'web']);
        $contentEditor->syncPermissions([
            'cms.posts.view', 'cms.posts.create', 'cms.posts.edit', 'cms.posts.delete',
            'cms.pages.view', 'cms.pages.create', 'cms.pages.edit', 'cms.pages.delete',
            'cms.partners.manage',
        ]);

        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'users.view', 'users.create', 'users.edit', 'users.assign-roles',
            'organizations.view', 'organizations.create', 'organizations.edit',
            'programs.view', 'programs.create', 'programs.edit',
            'calls.view', 'calls.create', 'calls.edit', 'calls.open', 'calls.close',
            'challenges.view', 'challenges.view-all', 'challenges.edit', 'challenges.publish', 'challenges.manage-backlog',
            'applications.view-all', 'applications.change-status', 'applications.request-supplement',
            'evaluations.view', 'evaluations.decide',
            'projects.view-all', 'mentorships.view', 'mentorships.assign',
            'milestones.view', 'milestones.approve',
            'documents.view', 'documents.upload', 'documents.delete',
            'notifications.send-bulk', 'notifications.manage-templates',
            'cms.posts.view', 'cms.posts.create', 'cms.posts.edit', 'cms.posts.delete',
            'cms.pages.view', 'cms.pages.create', 'cms.pages.edit', 'cms.pages.delete', 'cms.partners.manage',
            'reports.view', 'reports.create', 'exports.create', 'audit.view',
        ]);

        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());
    }
}
