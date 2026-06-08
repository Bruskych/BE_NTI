<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Http\Resources\EmailTemplateResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Spec 6.4: "e-mailové šablóny spravované administrátorom" — email templates managed by an administrator.
 * Routes are restricted to admin/super_admin via the parent route group.
 */
class EmailTemplateController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->api(EmailTemplateResource::collection(EmailTemplate::latest()->get()));
    }

    public function show(EmailTemplate $emailTemplate): JsonResponse
    {
        return response()->api(new EmailTemplateResource($emailTemplate));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'required|string|max:255|unique:email_templates,name',
            'subject'        => 'required|string|max:255',
            'body'           => 'required|string',
            'variables_json' => 'nullable|array',
            'variables_json.*' => 'string',
        ]);

        $template = EmailTemplate::create($validated);

        return response()->api(new EmailTemplateResource($template), 201);
    }

    public function update(Request $request, EmailTemplate $emailTemplate): JsonResponse
    {
        $validated = $request->validate([
            'name'           => 'sometimes|string|max:255|unique:email_templates,name,' . $emailTemplate->id,
            'subject'        => 'sometimes|string|max:255',
            'body'           => 'sometimes|string',
            'variables_json' => 'nullable|array',
            'variables_json.*' => 'string',
        ]);

        $emailTemplate->update($validated);

        return response()->api(new EmailTemplateResource($emailTemplate));
    }

    public function destroy(EmailTemplate $emailTemplate): JsonResponse
    {
        $emailTemplate->delete();

        return response()->api(null, 204);
    }
}
