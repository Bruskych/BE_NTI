<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;

Route::middleware('guest')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);
});

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
| Password Reset Route Placeholder
|--------------------------------------------------------------------------
| This named route is required by Laravel's Password Broker to generate
| the password reset URL included in the notification email.
*/
Route::get('/reset-password/{token}', function ($token) {
    return redirect('http://localhost:5173/reset-password?token=' . $token . '&email=' . request('email'));
})->name('password.reset');
