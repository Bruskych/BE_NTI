<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController, AdminController, ProfileController, ProgramController,
    CallController, ChallengeController, OrganizationController, SpecializationController,
    TeamController, ApplicationController, ProjectController, MilestoneController,
    ConsultationController, EvaluationController, NotificationController,
    ExportController, GdprController,
};

// -------------------------------------------------------------------------
// PUBLIC ROUTES
// -------------------------------------------------------------------------

Route::prefix('auth')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);
        Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    });
});

Route::get('/reset-password/{token}', function ($token) {
    return redirect('http://localhost:5173/reset-password?token=' . $token . '&email=' . request('email'));
})->name('password.reset');

Route::prefix('programs')->group(function () {
    Route::get('/', [ProgramController::class, 'index']);
    Route::get('/{id}', [ProgramController::class, 'show']);
});

Route::get('/specializations', [SpecializationController::class, 'index']);


// -------------------------------------------------------------------------
// PROTECTED ROUTES (Auth: Sanctum)
// -------------------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    // Auth & Profile
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/gdpr/export', [GdprController::class, 'exportMyData']);
        Route::delete('/gdpr/erase', [GdprController::class, 'eraseMyData']);
    });
    Route::put('/settings/update-profile', ProfileController::class);

    // Admin Routes
    Route::middleware('role:admin,super_admin')->prefix('admin')->group(function () {
        Route::get('/students/pending', [AdminController::class, 'pendingStudents']);
        Route::post('/students/{user}/approve', [AdminController::class, 'approveStudent']);
        Route::post('/students/{user}/reject', [AdminController::class, 'rejectStudent']);
        Route::get('/companies/pending', [AdminController::class, 'pendingCompanies']);
        Route::post('/companies/{id}/approve', [AdminController::class, 'approveCompany']);
        Route::post('/companies/{id}/reject', [AdminController::class, 'rejectCompany']);
        Route::get('/exports/types', [ExportController::class, 'types']);
        Route::get('/exports', [ExportController::class, 'index']);
        Route::post('/exports', [ExportController::class, 'store']);
        Route::post('/export/{resource}/{format}', [ExportController::class, 'storeByRoute']);
        Route::post('/gdpr/users/{user}/export', [GdprController::class, 'exportUserData']);
        Route::delete('/gdpr/users/{user}', [GdprController::class, 'eraseUserData']);
    });

    Route::get('/teams/my-team', [TeamController::class, 'myTeam']);
    Route::apiResource('teams', TeamController::class);
    Route::post('/teams/leave', [TeamController::class, 'leave']);

    Route::apiResource('challenges', ChallengeController::class);
    Route::apiResource('organizations', OrganizationController::class);
    Route::apiResource('teams', TeamController::class);
    Route::apiResource('applications', ApplicationController::class);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('consultations', ConsultationController::class);

    Route::prefix('applications/{application}')->group(function () {
        Route::post('/submit', [ApplicationController::class, 'submit']);
        Route::post('/evaluations', [EvaluationController::class, 'store']);
    });
    Route::prefix('calls')->group(function () {
        Route::get('/', [CallController::class, 'index']);
        Route::get('/{call}', [CallController::class, 'show']);
        Route::post('/', [CallController::class, 'store']);
        Route::put('/{call}', [CallController::class, 'update']);
    });

    Route::prefix('teams/{team}')->group(function () {
        Route::post('/invite', [TeamController::class, 'invite']);
    });

    // 1. Маршруты, привязанные к конкретному проекту (Коллекция)
    Route::prefix('projects/{project}/milestones')->group(function () {
        Route::get('/', [MilestoneController::class, 'index']);
        Route::post('/', [MilestoneController::class, 'store']);
    });

    // 2. Маршруты привязанные к конкретному майлстоуну (Ресурс)
    Route::prefix('milestones')->group(function () {
        Route::get('/{milestone}', [MilestoneController::class, 'show']);
        Route::put('/{milestone}', [MilestoneController::class, 'update']);
        Route::post('/{milestone}/approve', [MilestoneController::class, 'approve']);
    });

    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/{notification}/accept', [NotificationController::class, 'accept']);
        Route::post('/{notification}/reject', [NotificationController::class, 'reject']);
    });
});
