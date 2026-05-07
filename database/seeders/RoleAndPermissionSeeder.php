<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $permissions = [
            // Users
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.assign-roles',
            // Student profiles
            'student-profiles.view', 'student-profiles.edit',
            // Organizations
            'organizations.view', 'organizations.create', 'organizations.edit', 'organizations.delete',
            // Programs
            'programs.view', 'programs.create', 'programs.edit', 'programs.delete',
            // Calls (Program A)
            'calls.view', 'calls.create', 'calls.edit', 'calls.delete', 'calls.open', 'calls.close',
            // Challenges (Program B)
            'challenges.view', 'challenges.create', 'challenges.edit', 'challenges.delete',
            'challenges.publish', 'challenges.manage-backlog',
            // Teams
            'teams.view', 'teams.create', 'teams.edit', 'teams.delete', 'teams.invite-members',
            // Applications
            'applications.view-own', 'applications.view-all', 'applications.create',
            'applications.edit', 'applications.submit', 'applications.change-status',
            'applications.delete', 'applications.request-supplement',
            // Evaluations
            'evaluations.view', 'evaluations.create', 'evaluations.edit', 'evaluations.decide',
            // Projects
            'projects.view-own', 'projects.view-all', 'projects.edit',
            // Mentorships & Consultations
            'mentorships.view', 'mentorships.assign', 'consultations.create', 'consultations.edit',
            // Milestones
            'milestones.view', 'milestones.create', 'milestones.edit', 'milestones.approve',
            // Documents
            'documents.view', 'documents.upload', 'documents.delete',
            // Notifications
            'notifications.send-bulk', 'notifications.manage-templates',
            // CMS
            'cms.posts.view', 'cms.posts.create', 'cms.posts.edit', 'cms.posts.delete',
            'cms.pages.view', 'cms.pages.create', 'cms.pages.edit', 'cms.pages.delete',
            'cms.partners.manage',
            // Reporting
            'reports.view', 'reports.create', 'exports.create',
            // Audit & GDPR
            'audit.view', 'gdpr.manage',
            // System
            'system.settings', 'system.roles',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // 1. Návštevník
        Role::firstOrCreate(['name' => 'visitor', 'guard_name' => 'web']);

        // 2. Študent
        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $student->syncPermissions([
            'student-profiles.view', 'student-profiles.edit',
            'teams.view', 'teams.create',
            'applications.view-own', 'applications.create', 'applications.edit', 'applications.submit',
            'projects.view-own', 'milestones.view', 'documents.view', 'documents.upload',
        ]);

        // 3. Vedúci tímu
        $teamLeader = Role::firstOrCreate(['name' => 'team_leader', 'guard_name' => 'web']);
        $teamLeader->syncPermissions([
            'student-profiles.view', 'student-profiles.edit',
            'teams.view', 'teams.create', 'teams.edit', 'teams.invite-members',
            'applications.view-own', 'applications.create', 'applications.edit', 'applications.submit',
            'projects.view-own', 'milestones.view', 'documents.view', 'documents.upload',
        ]);

        // 4. Firma
        $company = Role::firstOrCreate(['name' => 'company', 'guard_name' => 'web']);
        $company->syncPermissions([
            'organizations.view', 'organizations.create', 'organizations.edit',
            'challenges.view', 'challenges.create', 'challenges.edit',
            'projects.view-own', 'milestones.view', 'documents.view', 'documents.upload',
        ]);

        // 5. Mentor
        $mentor = Role::firstOrCreate(['name' => 'mentor', 'guard_name' => 'web']);
        $mentor->syncPermissions([
            'projects.view-own', 'milestones.view', 'milestones.create',
            'milestones.edit', 'milestones.approve',
            'consultations.create', 'consultations.edit',
            'mentorships.view', 'documents.view', 'documents.upload',
        ]);

        // 6. Evaluator
        $evaluator = Role::firstOrCreate(['name' => 'evaluator', 'guard_name' => 'web']);
        $evaluator->syncPermissions([
            'applications.view-all', 'applications.request-supplement',
            'evaluations.view', 'evaluations.create', 'evaluations.edit', 'evaluations.decide',
            'documents.view',
        ]);

        // 7. Content editor
        $contentEditor = Role::firstOrCreate(['name' => 'content_editor', 'guard_name' => 'web']);
        $contentEditor->syncPermissions([
            'cms.posts.view', 'cms.posts.create', 'cms.posts.edit', 'cms.posts.delete',
            'cms.pages.view', 'cms.pages.create', 'cms.pages.edit', 'cms.pages.delete',
            'cms.partners.manage',
        ]);

        // 8. Admin
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'users.view', 'users.create', 'users.edit', 'users.assign-roles',
            'organizations.view', 'organizations.create', 'organizations.edit',
            'programs.view', 'programs.create', 'programs.edit',
            'calls.view', 'calls.create', 'calls.edit', 'calls.open', 'calls.close',
            'challenges.view', 'challenges.edit', 'challenges.publish', 'challenges.manage-backlog',
            'applications.view-all', 'applications.change-status', 'applications.request-supplement',
            'evaluations.view', 'evaluations.decide',
            'projects.view-all', 'mentorships.view', 'mentorships.assign',
            'milestones.view', 'milestones.approve',
            'documents.view', 'documents.upload', 'documents.delete',
            'notifications.send-bulk', 'notifications.manage-templates',
            'cms.posts.view', 'cms.posts.create', 'cms.posts.edit', 'cms.posts.delete',
            'cms.pages.view', 'cms.pages.create', 'cms.pages.edit', 'cms.pages.delete',
            'cms.partners.manage',
            'reports.view', 'reports.create', 'exports.create', 'audit.view',
        ]);

        // 9. Super admin
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());
    }
}
