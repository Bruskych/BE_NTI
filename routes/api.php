<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\ProgramController;
use App\Http\Controllers\CallController;
use App\Http\Controllers\ChallengeController;
use App\Http\Controllers\OrganizationController;
use App\Http\Controllers\SpecializationController;

use App\Http\Controllers\TeamController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\MilestoneController;
use App\Http\Controllers\ConsultationController;
use App\Http\Controllers\EvaluationController;

// Маршруты, доступные только для гостей (неавторизованных пользователей)
Route::prefix('auth')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    });

    // Маршруты, требующие обязательной авторизации через Sanctum
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/store', [AuthController::class, 'store']);
        Route::put('/update-profile', ProfileController::class);
    });
});

// Маршруты настроек профиля (Только для авторизованных)
Route::middleware(['auth:sanctum', 'not_role:visitor'])
    ->prefix('settings')
    ->group(function () {
        Route::put('/update-profile', ProfileController::class);
    });

// Административные маршруты с проверкой ролей (доступно для admin и super_admin)
Route::middleware(['auth:sanctum', 'role:admin,super_admin'])
    ->prefix('admin')
    ->group(function () {
        Route::get('/students/pending', [AdminController::class, 'pendingStudents']);
        Route::post('/students/{user}/approve', [AdminController::class, 'approveStudent']);
        Route::post('/students/{user}/reject', [AdminController::class, 'rejectStudent']);

        Route::get('/companies/pending', [AdminController::class, 'pendingCompanies']);
        Route::post('/companies/{id}/approve', [AdminController::class, 'approveCompany']);
        Route::post('/companies/{id}/reject', [AdminController::class, 'rejectCompany']);
    });

/*
|--------------------------------------------------------------------------
| Заглушка маршрута для сброса пароля
|--------------------------------------------------------------------------
| Этот именованный маршрут необходим компоненту Password Broker в Laravel
| для генерации корректного URL сброса пароля, отправляемого в email-уведомлении.
*/
Route::get('/reset-password/{token}', function ($token) {
    // Перенаправляем пользователя на фронтенд-приложение с передачей токена и email в параметрах
    return redirect('http://localhost:5173/reset-password?token=' . $token . '&email=' . request('email'));
})->name('password.reset');


// Список программ и конкретная программа
Route::prefix('programs')->group(function () {
    Route::get('/', [ProgramController::class, 'index']);
    Route::get('/{id}', [ProgramController::class, 'show']);
});

// Публичный список специализаций
Route::get('/specializations', [SpecializationController::class, 'index']);
/*
|--------------------------------------------------------------------------
| ЗАЩИЩЕННЫЕ МАРШРУТЫ (Требуется авторизация через Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {

    // Профиль организации (доступен авторизованным пользователям)
    Route::prefix('organizations')->group(function () {
        Route::get('/{organization}', [OrganizationController::class, 'show']);
        Route::put('/{organization}', [OrganizationController::class, 'update']);
    });

    // Челленджи (index и show доступны всем авторизованным, store и update — только для роли company)
    Route::prefix('challenges')->group(function () {
        Route::get('/', [ChallengeController::class, 'index']);
        Route::get('/{challenge}', [ChallengeController::class, 'show']);

        Route::middleware('role:company')->group(function () {
            Route::post('/', [ChallengeController::class, 'store']);
            Route::put('/{challenge}', [ChallengeController::class, 'update']);
        });
    });

    // Колы/Отборы (index и show доступны всем авторизованным, store и update — только для admin/super_admin)
    Route::prefix('calls')->group(function () {
        Route::get('/', [CallController::class, 'index']);
        Route::get('/{id}', [CallController::class, 'show']);

        Route::middleware('role:admin,super_admin')->group(function () {
            Route::post('/', [CallController::class, 'store']);
            Route::put('/{id}', [CallController::class, 'update']);
        });
    });

    Route::prefix('teams')->group(function () {
        Route::post('/', [TeamController::class, 'store']);
        Route::put('/{team}', [TeamController::class, 'update']);
        Route::post('/{team}/invite', [TeamController::class, 'invite']); // Не забудь добавить метод в TeamController
    });

    Route::prefix('applications')->group(function () {
        Route::post('/', [ApplicationController::class, 'store']);
        Route::put('/{application}', [ApplicationController::class, 'update']);
        Route::post('/{application}/submit', [ApplicationController::class, 'submit']);
        Route::patch('/{application}/status', [ApplicationController::class, 'updateStatus']); // Отдельный метод для смены статуса
    });

    Route::prefix('projects')->group(function () {
        Route::post('/', [ProjectController::class, 'store']);
        Route::get('/{project}', [ProjectController::class, 'show']);
        Route::put('/{project}', [ProjectController::class, 'update']);

        Route::post('/{project}/milestones', [MilestoneController::class, 'store']);
    });

    Route::prefix('milestones')->group(function () {
        Route::put('/{milestone}', [MilestoneController::class, 'update']);
        Route::post('/{milestone}/approve', [MilestoneController::class, 'approve']);
    });

    Route::prefix('consultations')->group(function () {
        Route::post('/', [ConsultationController::class, 'store']);
        Route::put('/{consultation}', [ConsultationController::class, 'update']);
    });

    Route::prefix('applications/{application}/evaluations')->group(function () {
        Route::post('/', [EvaluationController::class, 'store']);
    });

});


