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
                    'student_name'   => 'Заявка bez lídra (Preveriť team_user)',
                    'student_email'  => 'ID Tímu: ' . $app->team_id,
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

    // Одобрить заявку студента и выдать ему полноценную роль
    public function approveStudent($id, Request $request)
    {
        $application = Application::findOrFail($id);

        if ($application->status !== Application::STATUS_SUBMITTED) {
            return response()->json(['message' => 'Tento dopyt už nie je možné schváliť.'], 400);
        }

        try {
            DB::transaction(function () use ($application, $request) {
                // 1. Обновляем статус заявки на APPROVED
                $application->update([
                    'status' => Application::STATUS_APPROVED,
                    'approved_at' => now(),
                    'decision_comment' => $request->comment ?? 'Schválené administrátorom.',
                ]);

                // 2. Логируем смену статуса в историю заявок
                ApplicationHistory::create([
                    'application_id' => $application->id,
                    'old_status' => Application::STATUS_SUBMITTED,
                    'new_status' => Application::STATUS_APPROVED,
                    'changed_by' => auth()->id(), // ID админа
                    'comment' => $request->comment ?? 'Schválené administrátorom.',
                    'created_at' => now(),
                ]);

                // 3. Переводим пользователя из visitor в student
                $team = $application->team;
                if ($team) {
                    // Ищем ID лидера в таблице team_user
                    $leaderId = DB::table('team_user')
                        ->where('team_id', $team->id)
                        ->where('role', 'leader')
                        ->value('user_id');

                    if ($leaderId) {
                        $user = User::find($leaderId);
                        if ($user) {
                            // Spatie метод: удаляет старые роли (visitor) и записывает только 'student'
                            $user->syncRoles(['student']);
                        }
                    }
                }
            });
            return response()->json(['message' => 'Approved']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Chyba pri schvaľovaní.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // Отклонить заявку студента
    public function rejectStudent($id, Request $request)
    {
        $application = Application::findOrFail($id);

        if ($application->status !== Application::STATUS_SUBMITTED) {
            return response()->json(['message' => 'Tento dopyt už nie je možné zamietnuť.'], 400);
        }

        try {
            DB::transaction(function () use ($application, $request) {
                // 1. Обновляем статус заявки на REJECTED
                $application->update([
                    'status' => Application::STATUS_REJECTED,
                    'rejected_at' => now(),
                    'decision_comment' => $request->comment ?? 'Zamietnuté administrátorom.',
                ]);

                // 2. Логируем в историю
                ApplicationHistory::create([
                    'application_id' => $application->id,
                    'old_status' => Application::STATUS_SUBMITTED,
                    'new_status' => Application::STATUS_REJECTED,
                    'changed_by' => auth()->id(),
                    'comment' => $request->comment ?? 'Zamietnuté administrátorom.',
                    'created_at' => now(),
                ]);

                // При отклонении роль 'visitor' можно оставить без изменений,
                // чтобы у него на фронтенде висела плашка "Ваша заявка отклонена".
            });
            return response()->json(['message' => 'Rejected']);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Chyba pri zamietnutí.',
                'error'   => $e->getMessage()
            ], 500);
        }
    }

    // ======================================================
    // ЗАЯВКИ КОМПАНИЙ
    // ======================================================

    // Получить заявки компаний, которые ждут подтверждения
    public function pendingCompanies()
    {
        // Извлекаем заявки со статусом 'submitted', у которых organization_id НЕ равен NULL
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
                'company_name'   => $org ? $org->name : 'Neznáma firma',
                'company_tax_id' => $org ? $org->tax_id : 'Bez IČO',
                'sector'         => $org ? $org->sector : 'Nezadané',
                'website_link'   => $org ? $org->website_link : null,
                'description'    => $org ? $org->description : 'Bez popisu.',
                'owner_name'     => $owner ? $owner->name : 'Neznámy zástupca',
                'owner_email'    => $owner ? $owner->email : 'Bez emailu',
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
            return response()->json(['message' => 'Túto firemnú žiadosť už nie je možné schváliť.'], 400);
        }

        try {
            DB::transaction(function () use ($application, $request) {
                // 1. Обновляем статус заявки
                $application->update([
                    'status' => 'approved',
                    'approved_at' => now(),
                    'decision_comment' => $request->comment ?? 'Firma schválená administrátorom.',
                ]);

                // 2. Активируем саму компанию
                $application->organization->update([
                    'status' => 'active'
                ]);

                // 3. Логируем в историю изменений
                ApplicationHistory::create([
                    'application_id' => $application->id,
                    'old_status' => 'submitted',
                    'new_status' => 'approved',
                    'changed_by' => auth()->id(),
                    'comment' => $request->comment ?? 'Firma schválená administrátorom.',
                    'created_at' => now(),
                ]);

                // 4. Находим создателя компании (лидера технической команды) и даем ему роль 'company'
                $team = $application->team;
                if ($team) {
                    $ownerId = DB::table('team_user')
                        ->where('team_id', $team->id)
                        ->where('role', 'leader')
                        ->value('user_id');

                    if ($ownerId) {
                        $user = User::find($ownerId);
                        if ($user) {
                            $user->syncRoles(['company']); // Меняем 'visitor' на 'company'

                            // 5. Отправляем пользователю уведомление о том, что его фирму подтвердили
                            Notification::create([
                                'user_id' => $user->id,
                                'type' => 'company_registration_approved',
                                'channel' => 'system',
                                'title' => 'Firma bola schválená',
                                'message' => 'Vaša spoločnosť ' . $application->organization->name . ' bola úspešne overená. Teraz môžete pridávať zadania.',
                                'data_json' => json_encode(['organization_id' => $application->organization_id]),
                            ]);
                        }
                    }
                }
            });
            return response()->json(['message' => 'Company approved successfully']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Chyba pri schvaľovaní firmy.', 'error' => $e->getMessage()], 500);
        }
    }

    // Отклонить заявку компании
    public function rejectCompany($id, Request $request)
    {
        $application = Application::with('organization')->findOrFail($id);

        if ($application->status !== 'submitted' || !$application->organization_id) {
            return response()->json(['message' => 'Túto firemnú žiadosť už nie je možné zamietnuť.'], 400);
        }

        try {
            DB::transaction(function () use ($application, $request) {
                // 1. Ставим статус заявки REJECTED
                $application->update([
                    'status' => 'rejected',
                    'rejected_at' => now(),
                    'decision_comment' => $request->comment ?? 'Zamietnuté administrátorom.',
                ]);

                // 2. Оставляем статус организации 'inactive' (или меняем на 'deleted_at' при soft-delete)
                // Но лучше оставить неактивной, чтобы данные не терялись

                // 3. Пишем лог в историю
                ApplicationHistory::create([
                    'application_id' => $application->id,
                    'old_status' => 'submitted',
                    'new_status' => 'rejected',
                    'changed_by' => auth()->id(),
                    'comment' => $request->comment ?? 'Zamietnuté administrátorom.',
                    'created_at' => now(),
                ]);

                // 4. Оповещаем создателя, что регистрация отклонена
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
                            'title' => 'Registrácia firmy bola zamietnutá',
                            'message' => 'Ľutujeme, ale vaša žiadosť o registráciu firmy bola zamietnutá. Dôvod: ' . ($request->comment ?? 'Nesplnenie podmienok.'),
                            'data_json' => json_encode(['organization_id' => $application->organization_id]),
                        ]);
                    }
                }
            });
            return response()->json(['message' => 'Company rejected successfully']);

        } catch (\Exception $e) {
            return response()->json(['message' => 'Chyba pri zamietnutí firmy.', 'error' => $e->getMessage()], 500);
        }
    }
}
