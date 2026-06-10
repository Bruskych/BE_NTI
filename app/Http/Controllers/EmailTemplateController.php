<?php

namespace App\Http\Controllers;

use App\Models\EmailTemplate;
use App\Http\Resources\EmailTemplateResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use OpenApi\Attributes as OA;

/**
 * Spec 6.4: "e-mailové šablóny spravované administrátorom" — email templates managed by an administrator.
 * Routes are restricted to admin/super_admin via the parent route group.
 */
/** Контроллер email-шаблонов: CRUD для управляемых администратором шаблонов уведомлений */
class EmailTemplateController extends Controller
{
    /** Возвращает список всех email-шаблонов */
    #[OA\Get(
        path: '/admin/email-templates',
        summary: '[Admin] List email templates',
        tags: ['Email Templates'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 200, description: 'List of email templates'),
        ]
    )]
    public function index(): JsonResponse
    {
        return $this->apiJson([
            'data' => EmailTemplateResource::collection(EmailTemplate::latest()->get()),
        ]);
    }

    /** Возвращает один email-шаблон по идентификатору */
    #[OA\Get(
        path: '/admin/email-templates/{emailTemplate}',
        summary: '[Admin] Get a single email template',
        tags: ['Email Templates'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'emailTemplate', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Email template detail'),
            new OA\Response(response: 404, description: 'Email template not found'),
        ]
    )]
    public function show(EmailTemplate $emailTemplate): JsonResponse
    {
        return $this->apiJson(new EmailTemplateResource($emailTemplate));
    }

    /** Создаёт новый email-шаблон */
    #[OA\Post(
        path: '/admin/email-templates',
        summary: '[Admin] Create an email template',
        tags: ['Email Templates'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Email template created'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
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

        return $this->apiJson(new EmailTemplateResource($template), 201);
    }

    /** Обновляет существующий email-шаблон */
    #[OA\Put(
        path: '/admin/email-templates/{emailTemplate}',
        summary: '[Admin] Update an email template',
        tags: ['Email Templates'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'emailTemplate', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Email template updated'),
            new OA\Response(response: 422, description: 'Validation error'),
        ]
    )]
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

        return $this->apiJson(new EmailTemplateResource($emailTemplate));
    }

    /** Удаляет email-шаблон */
    #[OA\Delete(
        path: '/admin/email-templates/{emailTemplate}',
        summary: '[Admin] Delete an email template',
        tags: ['Email Templates'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'emailTemplate', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 204, description: 'Email template deleted'),
        ]
    )]
    public function destroy(EmailTemplate $emailTemplate): JsonResponse
    {
        $emailTemplate->delete();

        return $this->apiJson(null, 204);
    }
}
