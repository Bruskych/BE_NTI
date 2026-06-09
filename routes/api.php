<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\{
    AuthController, AdminController, ProfileController, ProgramController,
    CallController, ChallengeController, OrganizationController, SpecializationController,
    TeamController, ApplicationController, ProjectController, MilestoneController,
    ConsultationController, EvaluationController, NotificationController,
    NotificationPreferenceController, ExportController, GdprController, MentorshipController, PostController, PageController, DocumentController,
    BulkMessageController, PartnerController, EmailTemplateController, CookieController,
};

// -------------------------------------------------------------------------
// PUBLIC ROUTES
// -------------------------------------------------------------------------

Route::prefix('auth')->group(function () {
    Route::middleware(['guest', 'throttle:auth'])->group(function () {
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
    Route::get('/{program}/form-fields', [ProgramController::class, 'formFields']);
    Route::get('/{id}', [ProgramController::class, 'show']);
});

Route::get('/specializations', [SpecializationController::class, 'index']);

Route::prefix('partners')->group(function () {
    Route::get('/', [PartnerController::class, 'index']);
    Route::get('/{partner}', [PartnerController::class, 'show']);
});


// -------------------------------------------------------------------------
// PROTECTED ROUTES (Auth: Sanctum)
// -------------------------------------------------------------------------
Route::middleware('auth:sanctum')->group(function () {

    // Cookie files
    Route::post('/set-theme', [CookieController::class, 'setCookie']);
    Route::get('/get-theme', [CookieController::class, 'getCookie']);
    Route::post('/delete-theme', [CookieController::class, 'deleteCookie']);

    // Auth & Profile
    Route::prefix('auth')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::post('/email/verify', [AuthController::class, 'verifyEmail']);
        Route::post('/email/resend', [AuthController::class, 'resendEmailVerification']);
        Route::post('/gdpr/export', [GdprController::class, 'exportMyData']);
        Route::delete('/gdpr/erase', [GdprController::class, 'eraseMyData']);
    });
    Route::prefix('settings/update-profile')->group(function () {
        Route::post('/name', [ProfileController::class, 'updateName']);
        Route::post('/email', [ProfileController::class, 'updateEmail']);
        Route::post('/avatar', [ProfileController::class, 'updateAvatar']);
    });

    // Admin Routes
    Route::middleware('role:admin,super_admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
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

        Route::get('/bulk-messages', [BulkMessageController::class, 'index']);
        Route::post('/bulk-messages', [BulkMessageController::class, 'store']);
        Route::get('/bulk-messages/{bulk_message}', [BulkMessageController::class, 'show']);

        Route::apiResource('email-templates', EmailTemplateController::class)->except(['create', 'edit']);
    });

    // Teams
    Route::get('/teams/my-team', [TeamController::class, 'myTeam']);
    Route::post('/teams/leave', [TeamController::class, 'leave']);
    Route::apiResource('teams', TeamController::class);
    Route::prefix('teams/{team}')->group(function () {
        Route::post('/invite', [TeamController::class, 'invite']);
        Route::post('/remove-member', [TeamController::class, 'removeMember']);
    });

    // Resources
    Route::apiResource('challenges', ChallengeController::class);
    Route::apiResource('organizations', OrganizationController::class);
    Route::apiResource('applications', ApplicationController::class);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('consultations', ConsultationController::class);
    Route::apiResource('mentorships', MentorshipController::class);
    Route::apiResource('posts', PostController::class);
    Route::apiResource('pages', PageController::class);

    // Contextual actions
    Route::prefix('applications/{application}')->group(function () {
        Route::post('/submit', [ApplicationController::class, 'submit']);
        Route::post('/decide', [ApplicationController::class, 'decide']);
        Route::post('/evaluations', [EvaluationController::class, 'store']);
    });

    Route::prefix('calls')->group(function () {
        Route::get('/', [CallController::class, 'index']);
        Route::get('/{call}', [CallController::class, 'show']);
        Route::post('/', [CallController::class, 'store']);
        Route::put('/{call}', [CallController::class, 'update']);
    });

    // Milestones
    Route::prefix('projects/{project}/milestones')->group(function () {
        Route::get('/', [MilestoneController::class, 'index']);
        Route::post('/', [MilestoneController::class, 'store']);
    });
    Route::prefix('milestones')->group(function () {
        Route::get('/{milestone}', [MilestoneController::class, 'show']);
        Route::put('/{milestone}', [MilestoneController::class, 'update']);
        Route::post('/{milestone}/approve', [MilestoneController::class, 'approve']);
    });

    // Notifications and Preferecnes
    Route::prefix('notifications')->group(function () {
        Route::get('/', [NotificationController::class, 'index']);
        Route::post('/{notification}/accept', [NotificationController::class, 'accept']);
        Route::post('/{notification}/reject', [NotificationController::class, 'reject']);
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::delete('/{notification}', [NotificationController::class, 'destroy']);
        Route::delete('/', [NotificationController::class, 'destroyAll']);
    });

    Route::prefix('settings')->group(function () {
        Route::get('/notifications', [NotificationPreferenceController::class, 'show']);
        Route::patch('/notifications', [NotificationPreferenceController::class, 'update']);
    });

    // Documents
    Route::apiResource('documents', DocumentController::class);
    Route::prefix('documents')->group(function () {
        Route::get('/{document}/download', [DocumentController::class, 'download']);
        Route::post('/{document}/access-code', [DocumentController::class, 'requestAccessCode']);
        Route::get('/{document}/preview', [DocumentController::class, 'preview']);
        Route::post('/generate/internship-agreement', [DocumentController::class, 'generateInternshipAgreement']);
        Route::post('/templates/upload', [DocumentController::class, 'uploadTemplate']);
        Route::post('/templates/generate', [DocumentController::class, 'generateFromTemplate']);
    });
});
