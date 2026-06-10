<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AuthController, AdminController, ProfileController, ProgramController,
    CallController, ChallengeController, OrganizationController, SpecializationController,
    TeamController, ApplicationController, ProjectController, MilestoneController,
    ConsultationController, EvaluationController, NotificationController,
    NotificationPreferenceController, ExportController, GdprController, MentorshipController, PostController, PageController, DocumentController,
    BulkMessageController, PartnerController, EmailTemplateController, SitemapController, CookieController,
};

// -------------------------------------------------------------------------
// ПУБЛИЧНЫЕ МАРШРУТЫ — доступны без аутентификации
// -------------------------------------------------------------------------

// Аутентификация: регистрация, вход, сброс пароля
// Защита от брутфорса — ограничение частоты запросов (throttle:auth)
Route::prefix('auth')->group(function () {
    Route::middleware(['guest', 'throttle:auth'])->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
        Route::post('/reset-password', [AuthController::class, 'resetPassword']);
    });
});

// Редирект для сброса пароля — перенаправляет на страницу фронтенда с токеном
Route::get('/reset-password/{token}', function ($token) {
    return redirect(rtrim(config('app.frontend_url'), '/') . '/reset-password?token=' . $token . '&email=' . request('email'));
})->name('password.reset');

// Публичный просмотр программ и полей анкет (для страницы подачи заявок)
Route::prefix('programs')->group(function () {
    Route::get('/', [ProgramController::class, 'index']);
    Route::get('/{program}/form-fields', [ProgramController::class, 'formFields']);
    Route::get('/{id}', [ProgramController::class, 'show']);
});

// Публичный список специализаций (для фильтров и форм)
Route::get('/specializations', [SpecializationController::class, 'index']);

// Публичные страницы партнёров (лендинг)
Route::prefix('partners')->group(function () {
    Route::get('/', [PartnerController::class, 'index']);
    Route::get('/{partner}', [PartnerController::class, 'show']);
});

// Публичные CMS-страницы (About, Privacy Policy и т.д.)
Route::prefix('pages')->group(function () {
    Route::get('/', [PageController::class, 'index']);
    Route::get('/{page}', [PageController::class, 'show']);
});

// Публичный список открытых конкурсных отборов с дедлайнами (для главной страницы)
Route::get('/calls/open', [CallController::class, 'openCalls']);

// Публичные новости / посты блога
Route::prefix('posts')->group(function () {
    Route::get('/', [PostController::class, 'index']);
    Route::get('/{post}', [PostController::class, 'show']);
});

// Карта сайта в формате XML (для SEO-индексации)
Route::get('/sitemap.xml', [SitemapController::class, 'index']);


// -------------------------------------------------------------------------
// ЗАЩИЩЁННЫЕ МАРШРУТЫ — требуют аутентификации через Sanctum Bearer-токен
// -------------------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    // Настройки темы через Cookie
    Route::post('/set-theme', [CookieController::class, 'setCookie']);
    Route::get('/get-theme', [CookieController::class, 'getCookie']);
    Route::post('/delete-theme', [CookieController::class, 'deleteCookie']);

    // Профиль и аккаунт текущего пользователя
    // Верификация email, выход, экспорт и удаление данных GDPR
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/email/verify', [AuthController::class, 'verifyEmail']);
        Route::post('/email/resend', [AuthController::class, 'resendEmailVerification']);
        Route::post('/gdpr/export', [GdprController::class, 'exportMyData']);
        Route::delete('/gdpr/erase', [GdprController::class, 'eraseMyData']);
    });

    // Обновление профиля: имя, email, аватар (по отдельным маршрутам)
    Route::prefix('settings/update-profile')->group(function () {
        Route::post('/name', [ProfileController::class, 'updateName']);
        Route::post('/email', [ProfileController::class, 'updateEmail']);
        Route::post('/avatar', [ProfileController::class, 'updateAvatar']);
    });

    // Студенческий онбординг-профиль (учебная программа, курс, навыки, средний балл)
    Route::get('/settings/student-profile', [ProfileController::class, 'showStudentProfile']);
    Route::put('/settings/student-profile', [ProfileController::class, 'updateStudentProfile']);

    // -------------------------------------------------------------------------
    // МАРШРУТЫ АДМИНИСТРАТОРА — только для ролей admin и super_admin
    // -------------------------------------------------------------------------
    Route::middleware('role:admin,super_admin')->prefix('admin')->group(function () {

        // Дашборд и управление студентами (одобрение/отклонение)
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/students/pending', [AdminController::class, 'pendingStudents']);
        Route::post('/students/{id}/approve', [AdminController::class, 'approveStudent']);
        Route::post('/students/{id}/reject', [AdminController::class, 'rejectStudent']);

        // Управление компаниями (одобрение/отклонение регистраций)
        Route::get('/companies/pending', [AdminController::class, 'pendingCompanies']);
        Route::post('/companies/{id}/approve', [AdminController::class, 'approveCompany']);
        Route::post('/companies/{id}/reject', [AdminController::class, 'rejectCompany']);

        // Экспорт данных: список типов, журнал экспортов, создание экспорта
        Route::get('/exports/types', [ExportController::class, 'types']);
        Route::get('/exports', [ExportController::class, 'index']);
        Route::get('/exports/{exportsLog}/download', [ExportController::class, 'download']);
        Route::post('/exports', [ExportController::class, 'store']);
        Route::delete('/exports/{exportsLog}', [ExportController::class, 'destroy']);
        Route::post('/export/{resource}/{format}', [ExportController::class, 'storeByRoute']);

        // GDPR: экспорт и удаление данных произвольного пользователя
        Route::post('/gdpr/users/{user}/export', [GdprController::class, 'exportUserData']);
        Route::delete('/gdpr/users/{user}', [GdprController::class, 'eraseUserData']);

        // Массовые рассылки: создание, просмотр, список
        Route::get('/bulk-messages', [BulkMessageController::class, 'index']);
        Route::post('/bulk-messages', [BulkMessageController::class, 'store']);
        Route::get('/bulk-messages/{bulk_message}', [BulkMessageController::class, 'show']);

        // Шаблоны email-писем (CRUD без create/edit — API)
        Route::apiResource('email-templates', EmailTemplateController::class)->except(['create', 'edit']);
    });

    // -------------------------------------------------------------------------
    // КОМАНДЫ — управление студенческими командами
    // -------------------------------------------------------------------------
    Route::get('/teams/my-team', [TeamController::class, 'myTeam']);
    Route::post('/teams/leave', [TeamController::class, 'leave']);
    Route::apiResource('teams', TeamController::class);

    // Приглашение участников и удаление из команды
    Route::prefix('teams/{team}')->group(function () {
        Route::post('/invite', [TeamController::class, 'invite']);
        Route::post('/remove-member', [TeamController::class, 'removeMember']);
    });

    // -------------------------------------------------------------------------
    // РЕСУРСНЫЕ МАРШРУТЫ (CRUD) — основные сущности платформы
    // -------------------------------------------------------------------------
    Route::apiResource('challenges', ChallengeController::class);        // Челленджи компаний
    Route::apiResource('organizations', OrganizationController::class);  // Организации
    // Управление участниками организации (добавление, смена роли, удаление)
    Route::prefix('organizations/{organization}/members')->group(function () {
        Route::post('/', [OrganizationController::class, 'addMember']);
        Route::put('/{user}', [OrganizationController::class, 'updateMember']);
        Route::delete('/{user}', [OrganizationController::class, 'removeMember']);
    });
    Route::apiResource('applications', ApplicationController::class);    // Заявки
    Route::apiResource('projects', ProjectController::class);            // Проекты
    Route::apiResource('consultations', ConsultationController::class);  // Консультации
    Route::apiResource('mentorships', MentorshipController::class);      // Менторство

    // CMS: посты и страницы — запись защищена, чтение публично (выше)
    Route::apiResource('posts', PostController::class)->except(['index', 'show']);
    Route::apiResource('pages', PageController::class)->except(['index', 'show']);

    // -------------------------------------------------------------------------
    // КОНТЕКСТНЫЕ ДЕЙСТВИЯ НАД ЗАЯВКАМИ
    // -------------------------------------------------------------------------
    Route::prefix('applications/{application}')->group(function () {
        Route::post('/submit', [ApplicationController::class, 'submit']);    // Подать заявку
        Route::post('/decide', [ApplicationController::class, 'decide']);    // Одобрить/отклонить
        Route::post('/evaluations', [EvaluationController::class, 'store']); // Добавить оценку
    });

    // -------------------------------------------------------------------------
    // КОНКУРСНЫЕ ВЫЗОВЫ (CALLS) — жизненный цикл: draft → open → closed
    // -------------------------------------------------------------------------
    Route::prefix('calls')->group(function () {
        Route::get('/', [CallController::class, 'index']);
        Route::get('/{call}', [CallController::class, 'show']);
        Route::post('/', [CallController::class, 'store']);
        Route::put('/{call}', [CallController::class, 'update']);
        Route::delete('/{call}', [CallController::class, 'destroy']);
        Route::post('/{call}/open', [CallController::class, 'open']);    // Открыть приём заявок
        Route::post('/{call}/close', [CallController::class, 'close']); // Закрыть приём заявок
    });

    // -------------------------------------------------------------------------
    // ЭТАПЫ РАЗРАБОТКИ (MILESTONES) — привязаны к проекту
    // -------------------------------------------------------------------------
    Route::prefix('projects/{project}/milestones')->group(function () {
        Route::get('/', [MilestoneController::class, 'index']);   // Список этапов проекта
        Route::post('/', [MilestoneController::class, 'store']);  // Создать этап
    });
    Route::prefix('milestones')->group(function () {
        Route::get('/{milestone}', [MilestoneController::class, 'show']);              // Детали этапа
        Route::put('/{milestone}', [MilestoneController::class, 'update']);            // Обновить этап
        Route::post('/{milestone}/approve', [MilestoneController::class, 'approve']); // Подтвердить выполнение
    });

    // -------------------------------------------------------------------------
    // УВЕДОМЛЕНИЯ — системные оповещения пользователя
    // -------------------------------------------------------------------------
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);                         // Список уведомлений
        Route::post('/{notification}/accept', [NotificationController::class, 'accept']); // Принять (напр. инвайт в команду)
        Route::post('/{notification}/reject', [NotificationController::class, 'reject']); // Отклонить
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead']); // Пометить как прочитанное
        Route::delete('/{notification}', [NotificationController::class, 'destroy']);     // Удалить одно
        Route::delete('/', [NotificationController::class, 'destroyAll']);                // Удалить все
    });

    // Настройки предпочтений уведомлений пользователя
    Route::prefix('settings')->group(function () {
        Route::get('/notifications', [NotificationPreferenceController::class, 'show']);
        Route::patch('/notifications', [NotificationPreferenceController::class, 'update']);
    });

    // -------------------------------------------------------------------------
    // ДОКУМЕНТЫ — загрузка, просмотр, скачивание, генерация
    // -------------------------------------------------------------------------
    Route::apiResource('documents', DocumentController::class);
    Route::prefix('documents')->group(function () {
        Route::get('/{document}/download', [DocumentController::class, 'download']);               // Скачать файл
        Route::post('/{document}/access-code', [DocumentController::class, 'requestAccessCode']); // Запросить код доступа
        Route::get('/{document}/preview', [DocumentController::class, 'preview']);                 // Предпросмотр
        Route::post('/generate/internship-agreement', [DocumentController::class, 'generateInternshipAgreement']); // Генерация договора о практике
        Route::post('/templates/upload', [DocumentController::class, 'uploadTemplate']);           // Загрузить шаблон документа
        Route::post('/templates/generate', [DocumentController::class, 'generateFromTemplate']);   // Генерация из шаблона
    });
});
