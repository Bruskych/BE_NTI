<?php

namespace App\Http\Controllers;

use App\Mail\EmailVerificationMail;
use App\Models\User;
use App\Http\Requests\RegisterRequest; // Твой новый Request
use App\Actions\RegisterUserAction;    // Твой новый Action
use App\Services\EmailConfirmationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Регистрация нового пользователя.
     */
    public function register(RegisterRequest $request, RegisterUserAction $action)
    {
        $user = $action->execute($request->validated(), $request->ip());

        return response()->json([
            'message'       => 'Registration successful!',
            'token'         => $user->createToken('auth_token')->plainTextToken,
            'user'          => $user->load('roles'),
            'notifications' => $user->notifications()->latest()->get()
        ], 201);
    }

    /**
     * Авторизация пользователя (Вход системы).
     */
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

        return response()->json([
            'token'         => $token,
            'user'          => $user->load('roles'),
            'notifications' => $user->notifications()->latest()->get(),
        ]);
    }

    /**
     * Получение данных текущей сессии.
     */
    public function me(Request $request)
    {
        $user = $request->user();
        $user->load('roles');

        return response()->json([
            'user'          => $user,
            'notifications' => $user->notifications()->latest()->get()
        ]);
    }

    /**
     * Выход из системы.
     */
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()?->delete();
        }

        return response()->json(['message' => 'Logged out']);
    }

    /**
     * Запрос на восстановление пароля.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ]);

        $status = Password::sendResetLink($request->only('email'));

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => 'Reset link sent.'], 200)
            : response()->json(['message' => 'Failed to send email.'], 500);
    }

    /**
     * Подтверждение e-mail адреса по коду, отправленному при регистрации.
     * Spec 6.2: "registrácia e-mailom s overením adresy".
     */
    public function verifyEmail(Request $request, EmailConfirmationService $confirmation)
    {
        $request->validate([
            'code' => 'required|string',
        ]);

        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email is already verified.']);
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

        return response()->json([
            'message' => 'Email verified successfully.',
            'user'    => $user->fresh()->load('roles'),
        ]);
    }

    /**
     * Повторная отправка кода подтверждения e-mail.
     */
    public function resendEmailVerification(Request $request, EmailConfirmationService $confirmation)
    {
        $user = $request->user();

        if ($user->email_verified_at) {
            return response()->json(['message' => 'Email is already verified.']);
        }

        $code = $confirmation->generateCode($user->email, [], RegisterUserAction::EMAIL_VERIFICATION_PURPOSE);
        Mail::to($user->email)->queue(new EmailVerificationMail($user->name, $code, EmailConfirmationService::DEFAULT_EXPIRES_IN));

        return response()->json([
            'message'    => 'Verification code resent.',
            'expires_in' => EmailConfirmationService::DEFAULT_EXPIRES_IN,
        ]);
    }

    /**
     * Загрузка фото в профиль.
     */
    public function store(Request $request)
    {
        $request->validate([
            'photo' => 'required|image|mimes:jpeg,png,jpg|max:10240',
        ]);

        $user = $request->user();
        DB::transaction(function () use ($request, $user) {
            $path = $request->file('photo')->store('profile_photos', 'public');
            if ($user->avatar_path) {
                Storage::disk('public')->delete($user->avatar_path);
            }
            $user->update(['avatar_path' => $path]);
        });

        return response()->json([
            'message' => 'Avatar uploaded!',
            'user' => $user->load('roles')
        ]);
    }
}
