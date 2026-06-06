<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use App\Models\Application;
use App\Models\ApplicationHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdminController extends Controller
{
    // Главная страница админки (Dashboard)
    public function dashboard(Request $request)
    {
        return response()->json([
            'users_count' => User::count(),
            // Считаем новые заявки, которые ждут одобрения
            'pending_applications_count' => Application::where('status', Application::STATUS_SUBMITTED)->count(),
            // Последние 5 зарегистрированных пользователей
            'latest_users' => User::with('roles')->latest()->take(5)->get(),
        ]);
    }

    // Список всех пользователей системы с пагинацией
    public function users()
    {
        return response()->json(
            User::with('roles')->paginate(20)
        );
    }

    // ======================================================
    // ЗАЯВКИ СТУДЕНТОВ
    // ======================================================

    // Получить заявки студентов, которые ждут подтверждения
    public function pendingStudents()
    {
        // Берем заявки напрямую через Query Builder (DB), делая джоины к таблицам команд и пользователей
        $applications = DB::table('applications')
            ->join('teams', 'applications.team_id', '=', 'teams.id')
            ->join('team_user', 'teams.id', '=', 'team_user.team_id')
            ->join('users', 'team_user.user_id', '=', 'users.id')
            ->where('applications.status', 'submitted')
            ->where('team_user.role', 'leader')
            ->whereNull('applications.organization_id')
            ->whereNull('applications.deleted_at')
            ->select([
                'applications.id as application_id',
                'applications.status',
                'applications.submitted_at',
                'users.name as student_name',
                'users.email as student_email',
                'users.id as user_id'
            ])
            ->get();

        // Запасной план (fallback) на случай пустой таблицы или битых связей
        if ($applications->isEmpty()) {
            // Тут тоже обязательно добавляем whereNull, чтобы в запасном плане не вылезли компании
            $rawApplications = \App\Models\Application::where('status', 'submitted')
                ->whereNull('organization_id')
                ->get();

            $formatted = $rawApplications->map(function ($app) {
                return [
                    'application_id' => $app->id,
                    'status'         => $app->status,
                    'submitted_at'   => $app->submitted_at ? $app->submitted_at->toIso8601String() : null,
                    'student_name'   => 'Application without a leader (Check team_user)',
                    'student_email'  => 'Team ID: ' . $app->team_id,
                    'user_id'        => null
                ];
            });
            return response()->json($formatted);
        }

        // Если всё ок, форматируем даты в ISO стандарт для фронтенда
        $formatted = $applications->map(function ($app) {
            return [
                'application_id' => $app->application_id,
                'status'         => $app->status,
                'submitted_at'   => $app->submitted_at ? \Carbon\Carbon::parse($app->submitted_at)->toIso8601String() : null,
                'student_name'   => $app->student_name,
                'student_email'  => $app->student_email,
                'user_id'        => $app->user_id
            ];
        });
        return response()->json($formatted);
    }

    // Одобрить заявку студента и назначить постоянную должность
    public function approveStudent($id, Request $request)
    {
        $application = Application::findOrFail($id);

        if ($application->status !== Application::STATUS_SUBMITTED) {
            return response()->json(['message' => 'This request can no longer be approved.'], 400);
        }

        try {
            DB::transaction(function () use ($application, $request) {
                $comment = $request->comment ?? 'Student application approved by administrator.';

                // 1. Изменить статус заявки на «ОДОБРЕНО».
                $application->update([
                    'status' => Application::STATUS_APPROVED,
                    'approved_at' => now(),
                    'decision_comment' => $comment,
                ]);

                // 2. Изменение статуса записи в историю
                ApplicationHistory::create([
                    'application_id' => $application->id,
                    'old_status' => Application::STATUS_SUBMITTED,
                    'new_status' => Application::STATUS_APPROVED,
                    'changed_by' => auth()->id(),
                    'comment' => $comment,
                    'created_at' => now(),
                ]);

                // 3. Найдите студента, зарегистрировавшегося на конкурс (который является руководителем своей команды, подающей работу).
                $team = $application->team;
                if ($team) {
                    $studentId = DB::table('team_user')
                        ->where('team_id', $team->id)
                        ->where('role', 'leader')
                        ->value('user_id');

                    if ($studentId) {
                        $user = User::find($studentId);
                        if ($user) {
                            // Изменить роль с посетителя на студента.
                            $user->syncRoles(['student']);

                            // 4. Отправить системное уведомление на студенческий аккаунт
                            Notification::create([
                                'user_id' => $user->id,
                                'type' => 'student_application_approved',
                                'channel' => 'system',
                                'title' => 'Application approved ✅',
                                'message' => 'Your student application has been successfully verified. Welcome to the program! Comment: ' . $comment,
                                'data_json' => json_encode(['application_id' => $application->id]),
                            ]);
                        }
                    }
                }
            });
            return response()->json(['message' => 'Student approved successfully.']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error approving student.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Отклонить заявление студента
    public function rejectStudent($id, Request $request)
    {
        $application = Application::findOrFail($id);

        if ($application->status !== 'submitted' && $application->status !== Application::STATUS_SUBMITTED) {
            return response()->json(['message' => 'This request can no longer be denied.'], 400);
        }

        try {
            DB::transaction(function () use ($application, $request) {
                $comment = $request->comment ?? 'Student application rejected by administrator.';

                // 1. Изменить статус заявки на «ОТКЛОНЕНО».
                $application->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'decision_comment' => $comment,
                ]);

                // 2. Укажите, кто совершил это действие.
                $changedBy = auth()->id();

                if (!$changedBy && $application->team_id) {
                    $changedBy = DB::table('team_user')
                        ->where('team_id', $application->team_id)
                        ->where('role', 'leader')
                        ->value('user_id');
                }

                // 3. Запись изменений статуса в историю (с использованием фиксированной модели)
                ApplicationHistory::create([
                    'application_id' => $application->id,
                    'old_status' => 'submitted',
                    'new_status' => 'rejected',
                    'changed_by' => $changedBy,
                    'comment' => $comment,
                    'created_at' => now(),
                ]);

                // 4. Найдите учетную запись регистрирующегося студента и отправьте уведомление об отказе.
                if ($application->team_id) {
                    $studentId = DB::table('team_user')
                        ->where('team_id', $application->team_id)
                        ->where('role', 'leader')
                        ->value('user_id');

                    if ($studentId) {
                        Notification::create([
                            'user_id' => $studentId,
                            'type' => 'student_application_rejected',
                            'channel' => 'system',
                            'title' => 'Application rejected ❌',
                            'message' => 'Your student application has been rejected. Reason: ' . $comment,
                            'data_json' => json_encode(['application_id' => $application->id]),
                        ]);
                    }
                }
            });
            return response()->json(['message' => 'Student rejected successfully.']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error rejecting student.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ======================================================
    // ЗАЯВКИ КОМПАНИЙ
    // ======================================================

    // Получите заявки компаний, ожидающие подтверждения.
    public function pendingCompanies()
    {
        // Извлеките заявки со статусом 'submitted', в которых organization_id не равен NULL.
        $applications = Application::where('status', 'submitted')
            ->whereNotNull('organization_id')
            ->with(['organization', 'team.leader'])
            ->get();

        $formatted = $applications->map(function ($app) {
            $org = $app->organization;
            $owner = $app->team ? $app->team->leader : null;

            return [
                'application_id' => $app->id,
                'status'         => $app->status,
                'submitted_at'   => $app->submitted_at ? $app->submitted_at->toIso8601String() : null,
                'company_name'   => $org ? $org->name : 'Unknown company',
                'company_tax_id' => $org ? $org->tax_id : 'Without TAX ID',
                'sector'         => $org ? $org->sector : 'Unregistered sector',
                'website_link'   => $org ? $org->website_link : null,
                'description'    => $org ? $org->description : 'No description',
                'owner_name'     => $owner ? $owner->name : 'Unknown representative',
                'owner_email'    => $owner ? $owner->email : 'No email',
                'user_id'        => $owner ? $owner->id : null
            ];
        });
        return response()->json($formatted);
    }

    // Одобрить заявку компании
    public function approveCompany($id, Request $request)
    {
        $application = Application::with('organization')->findOrFail($id);

        if ($application->status !== 'submitted' || !$application->organization_id) {
            return response()->json(['message' => 'This business request can no longer be approved.'], 400);
        }

        try {
            DB::transaction(function () use ($application, $request) {
                $comment = $request->comment ?? 'Company approved by administrator.';

                // 1. Обновить статус приложения
                $application->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'decision_comment' => $comment,
                ]);

                // 2. Активировать саму организацию
                if ($application->organization) {
                    $application->organization->update([
                        'status' => 'active'
                    ]);
                }

                // 3. Войдите в историю изменений
                ApplicationHistory::create([
                    'application_id' => $application->id,
                    'old_status' => 'submitted',
                    'new_status' => 'approved',
                    'changed_by' => auth()->id(),
                    'comment' => $comment,
                    'created_at' => now(),
                ]);

                // 4. Найдите создателя компании и безопасно назначьте ему роль «компания».
                $team = $application->team;
                if ($team) {
                    $ownerId = DB::table('team_user')
                        ->where('team_id', $team->id)
                        ->where('role', 'leader')
                        ->value('user_id');

                    if ($ownerId) {
                        $user = User::find($ownerId);
                        if ($user) {
                            $user->syncRoles(['company']);

                            // 5. Отправить системное уведомление с комментарием администратора
                            Notification::create([
                                'user_id' => $user->id,
                                'type' => 'company_registration_approved',
                                'channel' => 'system',
                                'title' => 'Company registration approved ✅',
                                'message' => 'Your company "' . ($application->organization->name ?? 'Company') . '" has been successfully verified. You can now add new assignments. Comment: ' . $comment,
                                'data_json' => json_encode(['organization_id' => $application->organization_id]),
                            ]);
                        }
                    }
                }
            });
            return response()->json(['message' => 'Company approved successfully.']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error approving company.', 'error' => $e->getMessage()], 500);
        }
    }

    // Отклонить заявку компании
    public function rejectCompany($id, Request $request)
    {
        $application = Application::with('organization')->findOrFail($id);

        if ($application->status !== 'submitted' || !$application->organization_id) {
            return response()->json(['message' => 'This corporate request can no longer be denied.'], 400);
        }

        try {
            DB::transaction(function () use ($application, $request) {
                $comment = $request->comment ?? 'Company registration rejected by administrator.';

                // 1. Установить статус заявки на «ОТКЛОНЕНО».
                $application->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'decision_comment' => $comment,
                ]);

                // 2. Войдите в историю изменений
                ApplicationHistory::create([
                    'application_id' => $application->id,
                    'old_status' => 'submitted',
                    'new_status' => 'rejected',
                    'changed_by' => auth()->id(),
                    'comment' => $comment,
                    'created_at' => now(),
                ]);

                // 3. Уведомите создателя о том, что регистрация отклонена.
                $team = $application->team;
                if ($team) {
                    $ownerId = DB::table('team_user')
                        ->where('team_id', $team->id)
                        ->where('role', 'leader')
                        ->value('user_id');

                    if ($ownerId) {
                        Notification::create([
                            'user_id' => $ownerId,
                            'type' => 'company_registration_rejected',
                            'channel' => 'system',
                            'title' => 'Company registration rejected ❌',
                            'message' => 'Your company registration has been rejected. Reason: ' . $comment,
                            'data_json' => json_encode(['application_id' => $application->id]),
                        ]);
                    }
                }
            });
            return response()->json(['message' => 'Company rejected successfully.']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Error rejecting company.', 'error' => $e->getMessage()], 500);
        }
    }
}
