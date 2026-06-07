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

class GdprController extends Controller
{
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

        return response()->json([
            'message' => 'GDPR personal data export scheduled',
            'export' => $log,
        ], 202);
    }

    public function eraseMyData(Request $request)
    {
        $user = $request->user();
        $this->authorizeAdminOrSelf($user, $user);

        $this->anonymizeUser($user, $request->ip(), $request->userAgent());

        return response()->json([
            'message' => 'Personal data anonymization completed',
        ]);
    }

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

        return response()->json([
            'message' => 'GDPR personal data export scheduled for user',
            'export' => $log,
        ], 202);
    }

    public function eraseUserData(User $user, Request $request)
    {
        $this->authorizeAdmin($request->user());
        $this->anonymizeUser($user, $request->ip(), $request->userAgent());

        return response()->json([
            'message' => 'User personal data anonymized by admin',
            'user_id' => $user->id,
        ]);
    }

    protected function anonymizeUser(User $user, string $ipAddress = null, string $userAgent = null): void
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

    protected function authorizeAdmin(User $user): void
    {
        if (!$user->isAdmin()) {
            abort(403, 'Admin privileges required.');
        }
    }

    protected function authorizeAdminOrSelf(User $currentUser, User $targetUser): void
    {
        if ($currentUser->id !== $targetUser->id && !$currentUser->isAdmin()) {
            abort(403, 'You are not authorized to perform this action.');
        }
    }
}
