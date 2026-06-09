<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateExport;
use App\Models\AuditEvent;
use App\Models\ExportsLog;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use OpenApi\Attributes as OA;

/** Контроллер GDPR: экспорт и анонимизация персональных данных пользователей */
class GdprController extends Controller
{
    /** Ставит задание на экспорт персональных данных текущего пользователя */
    #[OA\Post(
        path: '/auth/gdpr/export',
        summary: 'Schedule a GDPR export of the current user\'s personal data',
        tags: ['GDPR'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 202, description: 'Export scheduled'),
        ]
    )]
    public function exportMyData(Request $request)
    {
        $user = $request->user();

        $log = ExportsLog::create([
            'user_id' => $user->id,
            'export_type' => 'personal_data_json',
            'filters_json' => ['user_id' => $user->id],
            'created_at' => now(),
        ]);

        GenerateExport::dispatch($log->id);

        return $this->apiJson([
            'message' => 'GDPR personal data export scheduled',
            'export' => $log,
        ], 202);
    }

    /** Анонимизирует персональные данные текущего пользователя (право на забвение) */
    #[OA\Delete(
        path: '/auth/gdpr/erase',
        summary: 'Anonymize/erase the current user\'s personal data ("right to be forgotten")',
        tags: ['GDPR'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'Personal data anonymized'),
            new OA\Response(response: 403, description: 'Not authorized'),
        ]
    )]
    public function eraseMyData(Request $request)
    {
        $user = $request->user();
        $this->authorizeAdminOrSelf($user, $user);

        $this->anonymizeUser($user, $request->ip(), $request->userAgent());

        return $this->apiJson([
            'message' => 'Personal data anonymization completed',
        ]);
    }

    /** Ставит задание на экспорт персональных данных указанного пользователя (для администратора) */
    #[OA\Post(
        path: '/admin/gdpr/users/{user}/export',
        summary: '[Admin] Schedule a GDPR export of a specific user\'s personal data',
        tags: ['GDPR'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 202, description: 'Export scheduled'),
            new OA\Response(response: 403, description: 'Admin privileges required'),
        ]
    )]
    public function exportUserData(User $user, Request $request)
    {
        $this->authorizeAdmin($request->user());

        $log = ExportsLog::create([
            'user_id' => $request->user()->id,
            'export_type' => 'personal_data_json',
            'filters_json' => ['user_id' => $user->id],
            'created_at' => now(),
        ]);

        GenerateExport::dispatch($log->id);

        return $this->apiJson([
            'message' => 'GDPR personal data export scheduled for user',
            'export' => $log,
        ], 202);
    }

    /** Анонимизирует персональные данные указанного пользователя (только для администратора) */
    #[OA\Delete(
        path: '/admin/gdpr/users/{user}',
        summary: '[Admin] Anonymize/erase a specific user\'s personal data',
        tags: ['GDPR'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'user', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'User personal data anonymized'),
            new OA\Response(response: 403, description: 'Admin privileges required'),
        ]
    )]
    public function eraseUserData(User $user, Request $request)
    {
        $this->authorizeAdmin($request->user());
        $this->anonymizeUser($user, $request->ip(), $request->userAgent());

        return $this->apiJson([
            'message' => 'User personal data anonymized by admin',
            'user_id' => $user->id,
        ]);
    }

    /** Удаляет связанные данные пользователя и заменяет личную информацию на анонимные значения */
    protected function anonymizeUser(User $user, ?string $ipAddress = null, ?string $userAgent = null): void
    {
        DB::transaction(function () use ($user, $ipAddress, $userAgent) {
            $user->studentProfile()->delete();
            $user->notificationPreference()->delete();
            $user->notifications()->delete();
            $user->uploadedDocuments()->delete();
            $user->posts()->delete();
            $user->bulkMessagesSent()->delete();
            $user->gdprConsents()->delete();

            $user->organizations()->detach();
            $user->teams()->detach();
            $user->syncRoles([]);

            $user->forceFill([
                'name' => 'Deleted user #' . $user->id,
                'email' => 'deleted+' . $user->id . '@nti.local',
                'avatar_path' => null,
                'password' => Hash::make(Str::random(48)),
                'email_verified_at' => null,
                'remember_token' => null,
            ])->save();

            AuditEvent::create([
                'user_id' => $user->id,
                'action' => 'gdpr_erase',
                'object_type' => 'user',
                'object_id' => $user->id,
                'old_values_json' => [],
                'new_values_json' => ['anonymized' => true],
                'ip_address' => $ipAddress,
                'user_agent' => $userAgent,
                'result' => 'success',
                'created_at' => now(),
            ]);
        });
    }

    /** Прерывает выполнение с 403, если пользователь не является администратором */
    protected function authorizeAdmin(User $user): void
    {
        if (!$user->isAdmin()) {
            abort(403, 'Admin privileges required.');
        }
    }

    /** Прерывает выполнение с 403, если текущий пользователь не является владельцем аккаунта или администратором */
    protected function authorizeAdminOrSelf(User $currentUser, User $targetUser): void
    {
        if ($currentUser->id !== $targetUser->id && !$currentUser->isAdmin()) {
            abort(403, 'You are not authorized to perform this action.');
        }
    }
}
