<?php

namespace App\Http\Controllers;

use App\Http\Resources\DocumentResource;
use App\Mail\DocumentAccessConfirmation;
use App\Models\Document;
use App\Services\DocumentService;
use App\Services\EmailConfirmationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Mail;
use OpenApi\Attributes as OA;

/** Контроллер документов: загрузка, скачивание, генерация PDF и управление шаблонами */
class DocumentController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
        $this->authorizeResource(Document::class, 'document');
    }

    /** Возвращает постраничный список документов с возможностью фильтрации */
    #[OA\Get(
        path: '/documents',
        summary: 'List documents (paginated), optionally filtered by application/project/milestone/type/classification',
        tags: ['Documents'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'application_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'project_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'milestone_id', in: 'query', required: false, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'type', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'classification', in: 'query', required: false, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Paginated list of documents'),
        ]
    )]
    public function index(Request $request): JsonResponse
    {
        $query = Document::query();

        if ($request->has('application_id')) {
            $query->where('application_id', $request->application_id);
        }

        if ($request->has('project_id')) {
            $query->where('project_id', $request->project_id);
        }

        if ($request->has('milestone_id')) {
            $query->where('milestone_id', $request->milestone_id);
        }

        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        if ($request->has('classification')) {
            $query->where('classification', $request->classification);
        }

        $documents = $query
            ->with('application', 'project')
            ->latest('created_at')
            ->paginate(20);

        return $this->apiJson(DocumentResource::collection($documents));
    }

    /** Загружает файл документа и сохраняет запись в БД */
    #[OA\Post(
        path: '/documents',
        summary: 'Upload a document, optionally linked to an application/project/milestone',
        tags: ['Documents'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Document uploaded'),
            new OA\Response(response: 403, description: 'Not authorized to upload documents'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Upload failed'),
        ]
    )]
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file'            => 'required|file|mimes:' . Document::ALLOWED_UPLOAD_EXTENSIONS . '|mimetypes:' . Document::ALLOWED_UPLOAD_MIMETYPES . '|max:10240',
            'application_id'  => 'nullable|exists:applications,id',
            'project_id'      => 'nullable|exists:projects,id',
            'milestone_id'    => 'nullable|exists:milestones,id',
            'type'            => 'nullable|string|max:50',
            'classification'  => 'nullable|in:' . implode(',', [
                Document::CLASSIFICATION_PUBLIC,
                Document::CLASSIFICATION_INTERNAL,
                Document::CLASSIFICATION_CONFIDENTIAL,
            ]),
        ]);

        try {
            $document = $this->documentService->upload(
                $request->file('file'),
                [
                    ...$validated,
                    'uploaded_by' => $request->user()->id,
                ]
            );

            return $this->apiJson([
                'message' => 'Document uploaded successfully',
                'document' => new DocumentResource($document),
            ], 201);
        } catch (\Exception $e) {
            return $this->apiJson([
                'message' => 'Failed to upload document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /** Возвращает детали одного документа */
    #[OA\Get(
        path: '/documents/{document}',
        summary: 'Get a single document with application and project',
        tags: ['Documents'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'document', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Document detail'),
            new OA\Response(response: 403, description: 'Not authorized to view this document'),
            new OA\Response(response: 404, description: 'Document not found'),
        ]
    )]
    public function show(Document $document): JsonResponse
    {
        return $this->apiJson(new DocumentResource($document->load('application', 'project')));
    }

    /** Отправляет код подтверждения на email для доступа к конфиденциальному документу */
    #[OA\Post(
        path: '/documents/{document}/access-code',
        summary: 'Request an email confirmation code needed to download a confidential document',
        tags: ['Documents'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'document', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Confirmation code sent to the user\'s email'),
            new OA\Response(response: 403, description: 'Not authorized to download this document'),
            new OA\Response(response: 422, description: 'Document is not confidential and needs no confirmation'),
        ]
    )]
    public function requestAccessCode(Request $request, Document $document, EmailConfirmationService $confirmation): JsonResponse
    {
        $this->authorize('download', $document);

        if (!$document->isConfidential()) {
            return $this->apiJson([
                'message' => 'This document does not require email confirmation. You can download it directly.',
            ], 422);
        }

        $user = $request->user();
        $code = $confirmation->generateCode($user->email, ['document_id' => $document->id]);

        Mail::to($user->email)->send(new DocumentAccessConfirmation(
            userEmail: $user->email,
            confirmationCode: $code,
            documentName: $document->file_name,
        ));

        return $this->apiJson([
            'message' => 'A confirmation code has been sent to your email. Provide it as ?code= when downloading.',
            'expires_in' => EmailConfirmationService::DEFAULT_EXPIRES_IN,
        ]);
    }

    /** Скачивает документ; конфиденциальные требуют предварительно полученный код подтверждения */
    #[OA\Get(
        path: '/documents/{document}/download',
        summary: 'Download a document (confidential documents require a ?code= confirmation code)',
        tags: ['Documents'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'document', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
            new OA\Parameter(name: 'code', in: 'query', required: false, description: 'Email confirmation code for confidential documents', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Document file stream'),
            new OA\Response(response: 403, description: 'Not authorized to download this document'),
            new OA\Response(response: 422, description: 'Invalid or expired confirmation code'),
            new OA\Response(response: 428, description: 'Confirmation code required for confidential document'),
            new OA\Response(response: 500, description: 'Download failed'),
        ]
    )]
    public function download(Request $request, Document $document, EmailConfirmationService $confirmation)
    {
        $this->authorize('download', $document);

        if ($document->isConfidential()) {
            $code = $request->query('code');

            if (!$code) {
                return $this->apiJson([
                    'message' => 'This document is confidential. Request a confirmation code via POST /documents/{document}/access-code, then retry with ?code=...',
                ], 428);
            }

            $confirmed = $confirmation->verify($request->user()->email, $code);

            if (!$confirmed || ($confirmed['document_id'] ?? null) !== $document->id) {
                return $this->apiJson([
                    'message' => 'Invalid or expired confirmation code.',
                ], 422);
            }
        }

        try {
            return $this->documentService->download($document);
        } catch (\Exception $e) {
            return $this->apiJson([
                'message' => 'Failed to download document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /** Возвращает временный URL для предпросмотра документа */
    #[OA\Get(
        path: '/documents/{document}/preview',
        summary: 'Get a temporary preview URL for a document',
        tags: ['Documents'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'document', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Preview URL'),
            new OA\Response(response: 403, description: 'Not authorized to view this document'),
            new OA\Response(response: 500, description: 'Failed to get preview'),
        ]
    )]
    public function preview(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        try {
            $url = $this->documentService->getPreviewUrl($document);
            return $this->apiJson(['url' => $url]);
        } catch (\Exception $e) {
            return $this->apiJson([
                'message' => 'Failed to get preview',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /** Загружает новую версию существующего документа */
    #[OA\Put(
        path: '/documents/{document}',
        summary: 'Upload a new version of an existing document',
        tags: ['Documents'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'document', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Document version updated'),
            new OA\Response(response: 403, description: 'Not authorized to update this document'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Update failed'),
        ]
    )]
    public function update(Request $request, Document $document): JsonResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:' . Document::ALLOWED_UPLOAD_EXTENSIONS . '|mimetypes:' . Document::ALLOWED_UPLOAD_MIMETYPES . '|max:10240',
            'classification' => 'nullable|in:' . implode(',', [
                Document::CLASSIFICATION_PUBLIC,
                Document::CLASSIFICATION_INTERNAL,
                Document::CLASSIFICATION_CONFIDENTIAL,
            ]),
        ]);

        try {
            $updatedDocument = $this->documentService->updateVersion(
                $request->file('file'),
                $document,
                $request->user()->id
            );

            return $this->apiJson([
                'message' => 'Document updated successfully',
                'document' => new DocumentResource($updatedDocument),
            ]);
        } catch (\Exception $e) {
            return $this->apiJson([
                'message' => 'Failed to update document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /** Удаляет документ из хранилища и базы данных */
    #[OA\Delete(
        path: '/documents/{document}',
        summary: 'Delete a document',
        tags: ['Documents'],
        security: [['sanctum' => []]],
        parameters: [
            new OA\Parameter(name: 'document', in: 'path', required: true, schema: new OA\Schema(type: 'integer')),
        ],
        responses: [
            new OA\Response(response: 200, description: 'Document deleted'),
            new OA\Response(response: 403, description: 'Not authorized to delete this document'),
            new OA\Response(response: 500, description: 'Deletion failed'),
        ]
    )]
    public function destroy(Document $document): JsonResponse
    {
        try {
            $this->documentService->delete($document);

            return $this->apiJson([
                'message' => 'Document deleted successfully',
            ]);
        } catch (\Exception $e) {
            return $this->apiJson([
                'message' => 'Failed to delete document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /** Генерирует PDF договора о практике на основе DOCX-шаблона */
    #[OA\Post(
        path: '/documents/generate/internship-agreement',
        summary: 'Generate an internship agreement PDF from a DOCX template',
        tags: ['Documents'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Agreement generated'),
            new OA\Response(response: 403, description: 'Not authorized to create documents'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Generation failed'),
        ]
    )]
    public function generateInternshipAgreement(Request $request): JsonResponse
    {
        $this->authorize('create', Document::class);

        $validated = $request->validate([
            'student_name' => 'required|string',
            'student_email' => 'required|email',
            'student_id_number' => 'required|string',
            'university' => 'required|string',
            'program' => 'required|string',
            'company_name' => 'required|string',
            'company_address' => 'required|string',
            'mentor_name' => 'required|string',
            'mentor_email' => 'required|email',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        try {
            $docxPath = resource_path('document-templates/Dohoda_o_odbornej_praxi_študenta-AI-final_1_10.docx');
            
            $document = $this->documentService->generatePdfFromDocx(
                $docxPath,
                [
                    ...$validated,
                    'uploaded_by' => $request->user()->id,
                ],
                "Internship_Agreement_{$validated['student_id_number']}"
            );

            return $this->apiJson([
                'message' => 'Internship agreement generated successfully',
                'document' => new DocumentResource($document),
                'download_url' => "/api/documents/{$document->id}/download",
            ], 201);
        } catch (\Exception $e) {
            return $this->apiJson([
                'message' => 'Failed to generate document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /** Загружает DOCX-шаблон документа */
    #[OA\Post(
        path: '/documents/templates/upload',
        summary: 'Upload a DOCX document template',
        tags: ['Documents'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Template uploaded'),
            new OA\Response(response: 403, description: 'Not authorized to create documents'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Upload failed'),
        ]
    )]
    public function uploadTemplate(Request $request): JsonResponse
    {
        $this->authorize('create', Document::class);

        $validated = $request->validate([
            'file' => 'required|file|mimes:docx|mimetypes:application/vnd.openxmlformats-officedocument.wordprocessingml.document|max:10240',
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        try {
            $document = $this->documentService->upload(
                $request->file('file'),
                [
                    'type' => 'template',
                    'classification' => Document::CLASSIFICATION_INTERNAL,
                    'uploaded_by' => $request->user()->id,
                ]
            );

            return $this->apiJson([
                'message' => 'Template uploaded successfully',
                'document' => new DocumentResource($document),
            ], 201);
        } catch (\Exception $e) {
            return $this->apiJson([
                'message' => 'Failed to upload template',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /** Генерирует PDF-документ, заполняя загруженный шаблон предоставленными данными */
    #[OA\Post(
        path: '/documents/templates/generate',
        summary: 'Generate a PDF document by filling in an uploaded template with data',
        tags: ['Documents'],
        security: [['sanctum' => []]],
        responses: [
            new OA\Response(response: 201, description: 'Document generated'),
            new OA\Response(response: 400, description: 'Referenced document is not a template'),
            new OA\Response(response: 403, description: 'Not authorized to create documents or view the template'),
            new OA\Response(response: 422, description: 'Validation error'),
            new OA\Response(response: 500, description: 'Generation failed'),
        ]
    )]
    public function generateFromTemplate(Request $request): JsonResponse
    {
        $this->authorize('create', Document::class);

        $validated = $request->validate([
            'template_id' => 'required|exists:documents,id',
            'data' => 'required|array',
        ]);

        try {
            $template = Document::findOrFail($validated['template_id']);
            $this->authorize('view', $template);

            if ($template->type !== 'template') {
                return $this->apiJson([
                    'message' => 'Document is not a template',
                ], 400);
            }

            $document = $this->documentService->generatePdfFromTemplate(
                $template,
                [
                    ...$validated['data'],
                    'uploaded_by' => $request->user()->id,
                ],
                $validated['data']['filename'] ?? 'generated_document'
            );

            return $this->apiJson([
                'message' => 'Document generated successfully',
                'document' => new DocumentResource($document),
                'download_url' => "/api/documents/{$document->id}/download",
            ], 201);
        } catch (\Exception $e) {
            return $this->apiJson([
                'message' => 'Failed to generate document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
