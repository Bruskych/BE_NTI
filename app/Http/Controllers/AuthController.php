<?php

namespace App\Http\Controllers;

use App\Models\Organization;
use App\Models\User;
use App\Models\Application;
use App\Models\Team;
use App\Models\Program;
use App\Models\Notification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Password;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Регистрация нового пользователя (Студент или Компания).
     */
    public function register(Request $request)
    {
        // Валидация входящего запроса на регистрацию
        $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|string|email|max:255|unique:users',
            'password'         => 'required|string|min:8|confirmed',
            'role'             => 'required|string|in:student,company,mentor',
            'company_name'     => 'required_if:role,company|string|max:255',
            'company_tax_id'   => 'required_if:role,company|string|regex:/^\d{8,10}$/',
            'sector'           => 'required_if:role,company|string|max:255',
            'website_link'     => 'required_if:role,company|url|max:500',
            'description'      => 'required_if:role,company|string|max:2000',
        ]);

        try {
            // Выполняем все связанные операции создания записей внутри одной БД-транзакции
            $result = DB::transaction(function () use ($request) {
                // Создание базовой учетной записи пользователя
                $user = User::create([
                    'name'     => $request->name,
                    'email'    => $request->email,
                    'password' => Hash::make($request->password),
                ]);

                // Логика регистрации для роли СТУДЕНТ
                if ($request->role === 'student') {
                    $user->assignRole('visitor');
                    $user->studentProfile()->create();

                    // Создаем персональную команду для студента
                    $team = Team::create([
                        'name'      => 'Team ' . $user->name,
                        'leader_id' => $user->id,
                        'status'    => 'active',
                    ]);

                    // Привязываем студента к созданной команде в качестве лидера
                    DB::table('team_user')->insert([
                        'team_id'   => $team->id,
                        'user_id'   => $user->id,
                        'role'      => 'leader',
                        'joined_at' => now(),
                    ]);

                    // Автоматически подаем заявку на участие в программе
                    Application::create([
                        'program_id'      => 1,
                        'organization_id' => null,
                        'team_id'         => $team->id,
                        'status'          => 'submitted',
                        'submitted_at'    => now(),
                        'total_score'     => 0.00,
                    ]);
                }
                // Логика регистрации для роли КОМПАНИЯ
                elseif ($request->role === 'company') {
                    $user->assignRole('visitor');

                    // Создаем карточку организации со статусом inactive (до верификации админом)
                    $organization = Organization::create([
                        'name'          => $request->company_name,
                        'tax_id'        => $request->company_tax_id,
                        'sector'        => $request->sector,
                        'website_link'  => $request->website_link,
                        'description'   => $request->description,
                        'status'        => 'inactive',
                    ]);

                    // Связываем пользователя с организацией в качестве владельца (owner)
                    DB::table('organization_user')->insert([
                        'organization_id' => $organization->id,
                        'user_id'         => $user->id,
                        'role'            => 'owner',
                        'created_at'      => now(),
                        'updated_at'      => now(),
                    ]);

                    // Создаем команду для компании со статусом ожидания (pending)
                    $team = Team::create([
                        'name'      => 'Team ' . $organization->name,
                        'leader_id' => $user->id,
                        'status'    => 'pending',
                    ]);

                    // Привязываем пользователя к команде компании
                    DB::table('team_user')->insert([
                        'team_id'   => $team->id,
                        'user_id'         => $user->id,
                        'role'            => 'leader',
                        'joined_at' => now(),
                    ]);

                    // Подаем заявку на верификацию компании модераторами проекта
                    Application::create([
                        'program_id'      => 1,
                        'organization_id' => $organization->id,
                        'team_id'         => $team->id,
                        'status'          => 'submitted',
                        'submitted_at'    => now(),
                        'total_score'     => 0.00,
                    ]);

                    // Создаем системное уведомление для пользователя
                    Notification::create([
                        'user_id'   => $user->id,
                        'type'      => 'company_registration_submitted',
                        'channel'   => 'system',
                        'title'     => 'Company registration submitted',
                        'message'   => 'Your company registration request has been submitted for administrator verification.',
                        'data_json' => json_encode(['organization_id' => $organization->id]),
                    ]);
                }

                // Выпускаем персональный токен доступа API
                $token = $user->createToken('auth_token')->plainTextToken;

                return [
                    'token'         => $token,
                    'user'          => $user->load('roles'),
                    'notifications' => $user->notifications()->latest()->get()
                ];
            });

            return response()->json([
                'message'       => 'Registration successful!',
                'token'         => $result['token'],
                'user'          => $result['user'],
                'notifications' => $result['notifications']
            ], 201);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error', 'error' => $e->getMessage()], 500);
        }
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

        // Проверяем существование пользователя и соответствие хэша пароля
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid email or password.'],
            ]);
        }

        // Выпускаем токен доступа при успешном совпадении данных
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'token'         => $token,
            'user'          => $user->load('roles'),
            'notifications' => $user->notifications()->latest()->get(),
        ]);
    }

    /**
     * Получение данных текущей сессии авторизованного пользователя.
     */
    public function me(Request $request)
    {
        $user = $request->user();

        if (!$user) {
            return response()->json([
                'message' => 'Unauthenticated'
            ], 401);
        }

        $user->load('roles');

        return response()->json([
            'user'          => $user,
            'notifications' => $user->notifications()->latest()->get()
        ]);
    }

    /**
     * Выход из системы (Отзыв текущего токена доступа).
     */
    public function logout(Request $request)
    {
        if ($request->user()) {
            $request->user()->currentAccessToken()?->delete();
        }

        return response()->json([
            'message' => 'Logged out'
        ]);
    }

    /**
     * Запрос ссылки на восстановление/сброс забытого пароля.
     */
    public function forgotPassword(Request $request)
    {
        $request->validate([
            'email' => 'required|email|exists:users,email'
        ], [
            'email.exists' => 'User with this email address does not exist.'
        ]);

        try {
            // Отправляем ссылку сброса через стандартный брокер Laravel
            $status = Password::sendResetLink($request->only('email'));

            if ($status === Password::RESET_LINK_SENT) {
                return response()->json([
                    'message' => 'A password reset link has been sent to your email address.'
                ], 200);
            }

            return response()->json([
                'message' => 'Failed to send password reset email.'
            ], 500);

        } catch (\Exception $e) {
            // Логируем непредвиденную ошибку на сервере
            \Log::error('Password Reset Link Error: ' . $e->getMessage());

            return response()->json([
                'message' => 'Server error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Проверка/загрузка фото в профиль.
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
        $user->load('roles');
        return response()->json([
            'message' => 'Avatar uploaded!',
            'user' => $user
        ]);
    }
}
