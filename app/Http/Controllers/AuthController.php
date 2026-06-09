<?php

namespace App\Http\Controllers;

use App\Mail\EmailVerificationMail;
use App\Models\User;
use App\Http\Requests\RegisterRequest; // Твой новый Request
use App\Actions\RegisterUserAction;    // Твой новый Action
use App\Services\EmailConfirmationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;
use OpenApi\Attributes as OA;

/** Контроллер аутентификации: регистрация, вход, выход, верификация email и сброс пароля */
class AuthController extends Controller
{
    /**
     * Регистрация нового пользователя.
     */
    #[OA\Post(
        path: '/auth/register',
        summary: 'Register a new account (student, mentor, company or internal role)',
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 201, description: 'Account created, returns auth token and user'),
            new OA\Response(response: 422, description: 'Validation error (e.g. missing GDPR consent)'),
        ]
    )]
    public function register(RegisterRequest $request, RegisterUserAction $action)
    {
        $user = $action->execute($request->validated(), $request->ip());

        return $this->apiJson([
            'message'       => 'Registration successful!',
            'token'         => $user->createToken('auth_token')->plainTextToken,
            'user'          => $user->load('roles'),
            'notifications' => $user->notifications()->latest()->get()
        ], 201);
    }

    /**
     * Авторизация пользователя (Вход системы).
     */
    #[OA\Post(
        path: '/auth/login',
        summary: 'Authenticate with email and password and obtain an API token',
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Authenticated, returns token and user'),
            new OA\Response(response: 422, description: 'Invalid credentials'),
            new OA\Response(response: 429, description: 'Too many login attempts (rate limited)'),
        ]
    )]
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return $this->apiJson([
            'token'         => $token,
            'user'          => $user->load('roles'),
            'notifications' => $user->notifications()->latest()->get(),
        ]);
    }

    /**
     * Получение данных текущей сессии.
     */
    #[OA\Get(
        path: '/auth/me',
        summary: 'Get the currently authenticated user and their notifications',
        tags: ['Auth'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Current user with roles and notifications'),
            new OA\Response(response: 401, description: 'Unauthenticated'),
        ]
    )]
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('roles');

        return $this->apiJson([
            'user'          => $user,
            'notifications' => $user->notifications()->latest()->get()
        ]);
    }

    /**
     * Выход из системы.
     */
    #[OA\Post(
        path: '/auth/logout',
        summary: 'Revoke the current API token (log out)',
        tags: ['Auth'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Logged out'),
        ]
    )]
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()?->delete();
        }

        return $this->apiJson(['message' => 'Logged out']);
    }

    /**
     * Запрос на восстановление пароля.
     */
    #[OA\Post(
        path: '/auth/forgot-password',
        summary: 'Send a password reset link to the given email address',
        tags: ['Auth'],
        responses: [
            new OA\Response(response: 200, description: 'Reset link sent'),
            new OA\Response(response: 422, description: 'Validation error (unknown email)'),
            new OA\Response(response: 500, description: 'Failed to send email'),
        ]
    )]
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? $this->apiJson(['message' => 'Reset link sent.'], 200)
            : $this->apiJson(['message' => 'Failed to send email.'], 500);
    }

    /**
     * Подтверждение e-mail адреса по коду, отправленному при регистрации.
     * Spec 6.2: "registrácia e-mailom s overením adresy".
     */
    #[OA\Post(
        path: '/auth/email/verify',
        summary: 'Confirm the email address using the verification code sent at registration',
        tags: ['Auth'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Email verified (or already verified)'),
            new OA\Response(response: 422, description: 'Code is invalid or expired'),
        ]
    )]
    public function verifyEmail(Request $request, EmailConfirmationService $confirmation)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();

        if ($user->email_verified_at) {
            return $this->apiJson(['message' => 'Email is already verified.']);
        }

        $confirmed = $confirmation->verify($user->email, $request->input('code'), RegisterUserAction::EMAIL_VERIFICATION_PURPOSE);

        if ($confirmed === null) {
            throw ValidationException::withMessages([
                'code' => ['The verification code is invalid or has expired.'],
            ]);
        }

        // email_verified_at is intentionally not mass-assignable (it must never be set from user input);
        // forceFill bypasses that guard for this internal, code-verified update.
        $user->forceFill(['email_verified_at' => now()])->save();

        return $this->apiJson([
            'message' => 'Email verified successfully.',
            'user'    => $user->fresh()->load('roles'),
        ]);
    }

    /**
     * Повторная отправка кода подтверждения e-mail.
     */
    #[OA\Post(
        path: '/auth/email/resend',
        summary: 'Resend the email verification code',
        tags: ['Auth'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Verification code resent (or already verified)'),
        ]
    )]
    public function resendEmailVerification(Request $request, EmailConfirmationService $confirmation)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return $this->apiJson(['message' => 'Email is already verified.']);
        }

        $code = $confirmation->generateCode($user->email, [], RegisterUserAction::EMAIL_VERIFICATION_PURPOSE);
        Mail::to($user->email)->queue(new EmailVerificationMail($user->name, $code, EmailConfirmationService::DEFAULT_EXPIRES_IN));

        return $this->apiJson([
            'message'    => 'Verification code resent.',
            'expires_in' => EmailConfirmationService::DEFAULT_EXPIRES_IN,
        ]);
    }

}
