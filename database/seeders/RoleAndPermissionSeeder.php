<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleAndPermissionSeeder extends Seeder
{
    /**
     * Роли и разрешения (Policies)
     */
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();
        $permissions = [
            // Управление пользователями
            'users.view', 'users.create', 'users.edit', 'users.delete', 'users.assign-roles',
            'student-profiles.view', 'student-profiles.edit',

            // Организации (Компании)
            'organizations.view', 'organizations.create', 'organizations.edit', 'organizations.delete',

            // Программы (Гранты, практики)
            'programs.view', 'programs.create', 'programs.edit', 'programs.delete',

            // Конкурсы / Вызовы (Calls)
            'calls.view', 'calls.create', 'calls.edit', 'calls.delete', 'calls.open', 'calls.close',

            // Челленджи / Задачи компаний
            'challenges.view', 'challenges.view-all', 'challenges.create', 'challenges.edit', 'challenges.edit-all', 'challenges.delete', 'challenges.publish', 'challenges.manage-backlog',

            // Команды
            'teams.view', 'teams.create', 'teams.edit', 'teams.delete', 'teams.invite-members',

            // Заявки (Applications)
            'applications.view-own', 'applications.view-all', 'applications.create', 'applications.edit', 'applications.submit', 'applications.change-status', 'applications.delete', 'applications.request-supplement',

            // Оценивание (Evaluations)
            'evaluations.view', 'evaluations.create', 'evaluations.edit', 'evaluations.decide',

            // Проекты
            'projects.view-own', 'projects.view-all', 'projects.create', 'projects.edit', 'projects.delete',

            // Менторство и Консультации
            'mentorships.view', 'mentorships.assign', 'consultations.view', 'consultations.create', 'consultations.edit', 'consultations.view-own', 'consultations.edit-own', 'consultations.delete-own',

            // Вехи / Этапы разработки (Milestones)
            'milestones.view', 'milestones.create', 'milestones.edit', 'milestones.approve',

            // Документы
            'documents.view', 'documents.upload', 'documents.delete',

            // Уведомления и CMS контент
            'notifications.send-bulk', 'notifications.manage-templates',
            'cms.posts.view', 'cms.posts.create', 'cms.posts.edit', 'cms.posts.delete',
            'cms.pages.view', 'cms.pages.create', 'cms.pages.edit', 'cms.pages.delete', 'cms.partners.manage',

            // Аналитика, логи и системные настройки
            'reports.view', 'reports.create', 'exports.create',
            'audit.view', 'gdpr.manage',
            'system.settings', 'system.roles',
        ];


        // ------------------------------
        // Ручное создание
        // ------------------------------

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web'
            ]);
        }

        // 1. Гость системы
        Role::firstOrCreate(['name' => 'visitor', 'guard_name' => 'web']);

        // 2. Студент
        $student = Role::firstOrCreate(['name' => 'student', 'guard_name' => 'web']);
        $student->syncPermissions([
            'student-profiles.view', 'student-profiles.edit',
            'teams.view', 'teams.create',
            'applications.view-own', 'applications.create', 'applications.edit',
            'projects.view-own', 'milestones.view', 'consultations.view', 'documents.view', 'documents.upload',
        ]);

        // 3. Лидер команды (получает доп. права на отправку заявок и инвайты)
        $teamLeader = Role::firstOrCreate(['name' => 'team_leader', 'guard_name' => 'web']);
        $teamLeader->syncPermissions([
            'student-profiles.view', 'student-profiles.edit',
            'teams.view', 'teams.create', 'teams.edit', 'teams.invite-members', 'teams.delete',
            'applications.view-own', 'applications.create', 'applications.edit', 'applications.submit', 'applications.delete',
            'projects.view-own', 'milestones.view', 'consultations.view', 'documents.view', 'documents.upload',
        ]);

        // 4. Представитель Компании (Заказчик челленджей)
        $company = Role::firstOrCreate(['name' => 'company', 'guard_name' => 'web']);
        $company->syncPermissions([
            'organizations.view', 'organizations.edit',
            'challenges.view', 'challenges.create', 'challenges.edit', 'challenges.delete',
            'projects.view-own', 'milestones.view', 'documents.view', 'documents.upload',
        ]);

        // 5. Ментор / Трекер проектов
        $mentor = Role::firstOrCreate(['name' => 'mentor', 'guard_name' => 'web']);
        $mentor->syncPermissions([
            'projects.view-own', 'milestones.view', 'milestones.create', 'milestones.edit', 'milestones.approve',
            'consultations.view', 'consultations.create', 'consultations.edit', 'consultations.view-own', 'consultations.edit-own', 'consultations.delete-own',
            'mentorships.view', 'documents.view', 'documents.upload',
        ]);

        // 6. Эксперт / Оцениватель заявок (Evaluator)
        $evaluator = Role::firstOrCreate(['name' => 'evaluator', 'guard_name' => 'web']);
        $evaluator->syncPermissions([
            'applications.view-all', 'applications.request-supplement',
            'evaluations.view', 'evaluations.create', 'evaluations.edit', 'evaluations.decide',
            'documents.view',
        ]);

        // 7. Контент-менеджер
        $contentEditor = Role::firstOrCreate(['name' => 'content_editor', 'guard_name' => 'web']);
        $contentEditor->syncPermissions([
            'cms.posts.view', 'cms.posts.create', 'cms.posts.edit', 'cms.posts.delete',
            'cms.pages.view', 'cms.pages.create', 'cms.pages.edit', 'cms.pages.delete',
            'cms.partners.manage',
        ]);

        // 8. Администратор платформы
        $admin = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);
        $admin->syncPermissions([
            'users.view', 'users.create', 'users.edit', 'users.assign-roles',
            'organizations.view', 'organizations.create', 'organizations.edit', 'organizations.delete',
            'programs.view', 'programs.create', 'programs.edit', 'programs.delete',
            'calls.view', 'calls.create', 'calls.edit', 'calls.open', 'calls.close', 'calls.delete',
            'challenges.view', 'challenges.view-all', 'challenges.create', 'challenges.edit', 'challenges.edit-all', 'challenges.delete', 'challenges.publish', 'challenges.manage-backlog',
            'applications.view-all', 'applications.change-status', 'applications.request-supplement',
            'evaluations.view', 'evaluations.decide',
            'projects.view-all', 'projects.create', 'projects.edit', 'projects.delete',
            'mentorships.view', 'mentorships.assign',
            'milestones.view', 'milestones.approve',
            'documents.view', 'documents.upload', 'documents.delete',
            'notifications.send-bulk', 'notifications.manage-templates',
            'cms.posts.view', 'cms.posts.create', 'cms.posts.edit', 'cms.posts.delete',
            'cms.pages.view', 'cms.pages.create', 'cms.pages.edit', 'cms.pages.delete', 'cms.partners.manage',
            'reports.view', 'reports.create', 'exports.create', 'audit.view',
        ]);

        // 9. Супер-администратор (Полный доступ ко всему через Gate::before)
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin', 'guard_name' => 'web']);
        $superAdmin->syncPermissions(Permission::all());
    }
}
