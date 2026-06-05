<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

// Маршруты, доступные только для гостей (неавторизованных пользователей)
Route::middleware('guest')->group(function () {
    // чтобы не ломать текущую конфигурацию Axios на фронтенде.
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
});

// Маршруты, требующие обязательной авторизации через Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
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
    // УЛУЧШЕНИЕ: Берем URL фронтенда из .env (по умолчанию localhost:5173),
    // чтобы при деплое на реальный сервер ссылка не вела на localhost.
    $frontendUrl = env('FRONTEND_URL', 'http://localhost:5173');
    return redirect($frontendUrl . '/reset-password?token=' . $token . '&email=' . request('email'));
})->name('password.reset');
