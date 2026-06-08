<?php

namespace App\Http\Controllers;

use App\Models\NotificationPreference;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationPreferenceController extends Controller
{
    public function show(Request $request): JsonResponse
    {
        $prefs = $request->user()->notificationPreference ?? new NotificationPreference([
            'email_enabled' => true,
            'system_enabled' => true,
            'marketing_enabled' => false,
            'deadline_alerts_enabled' => true,
        ]);

        if ($prefs->exists) {
            $this->authorize('view', $prefs);
        }

        return response()->json(['data' => $prefs]);
    }

    public function update(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email_enabled'           => 'boolean',
            'system_enabled'          => 'boolean',
            'marketing_enabled'       => 'boolean',
            'deadline_alerts_enabled' => 'boolean',
        ]);

        $prefs = $request->user()->notificationPreference;

        if ($prefs) {
            $this->authorize('update', $prefs);
        }

        $prefs = NotificationPreference::updateOrCreate(
            ['user_id' => $request->user()->id],
            $validated
        );

        return response()->json([
            'message' => 'Preferences updated successfully.',
            'data'    => $prefs
        ]);
    }
}
