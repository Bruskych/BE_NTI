<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Вызов сидеров для заполнения базы данных
     */
    public function run(): void
    {
        // СТАДИЯ 1
        $this->call([
            RoleAndPermissionSeeder::class,     // Роли и права (Spatie Permissions)
            UserSeeder::class,                  // Пользователи системы (админы, менторы, студенты)
            ProgramSeeder::class,               // Программы (гранты, практики)
            OrganizationSeeder::class,          // Организации (компании)
            SpecializationSeeder::class,        // Направления (Frontend, Backend, Design)
        ]);

        // СТАДИЯ 2
        $this->call([
            StudentProfileSeeder::class,        // Расширенные профили студентов (зависят от User)
            EvaluationTemplateSeeder::class,    // Шаблоны и критерии оценивания (зависят от Program)
            CallSeeder::class,                  // Конкурсы / Вызовы (зависят от Program и Template)
            FormFieldSeeder::class,             // Конструктор анкет (зависит от Program и Call)
            ChallengeSeeder::class,             // Челленджи компаний (зависят от Program, Organization, User)
            TeamSeeder::class,                  // Студенческие команды (зависят от User)
            CmsAndPartnerSeeder::class,         // Контент CMS: страницы, новости, партнеры
        ]);

        // СТАДИЯ 3
        $this->call([
            ApplicationSeeder::class,           // Заявки от Команд на Вызовы/Челленджи
        ]);

        // СТАДИЯ 4
        $this->call([
            EvaluationSeeder::class,            // Оценки экспертов (строго ПОСЛЕ появления заявок!)
            ProjectSeeder::class,               // Проекты, созданные для одобренных заявок
        ]);

        // СТАДИЯ 5
        $this->call([
            MentorshipSeeder::class,            // Закрепление менторов за Проектами
            MilestoneSeeder::class,             // Этапы разработки и дедлайны внутри Проектов
            DocumentSeeder::class,              // Документы (паспорта, договоры, отчеты по этапам)
            NotificationSeeder::class,          // Уведомления, шаблоны писем и массовые рассылки
            GdprAndAuditSeeder::class,          // Технические логи: согласия GDPR и аудит действий
            ReportAndExportSeeder::class,       // Аналитические отчеты и логи экспорта данных
        ]);
    }
}
