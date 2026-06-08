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

class DocumentController extends Controller
{
    protected DocumentService $documentService;

    public function __construct(DocumentService $documentService)
    {
        $this->documentService = $documentService;
        $this->authorizeResource(Document::class, 'document');
    }

    /**
     * List documents for a resource
     */
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

        return response()->api(DocumentResource::collection($documents));
    }

    /**
     * Store uploaded document
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'file'            => 'required|file|max:10240', // 10MB
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

            return response()->api([
                'message' => 'Document uploaded successfully',
                'document' => new DocumentResource($document),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get single document
     */
    public function show(Document $document): JsonResponse
    {
        return response()->api(new DocumentResource($document->load('application', 'project')));
    }

    /**
     * Send an email confirmation code required to download confidential documents
     */
    public function requestAccessCode(Request $request, Document $document, EmailConfirmationService $confirmation): JsonResponse
    {
        $this->authorize('download', $document);

        if (!$document->isConfidential()) {
            return response()->json([
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

        return response()->json([
            'message' => 'A confirmation code has been sent to your email. Provide it as ?code= when downloading.',
            'expires_in' => EmailConfirmationService::DEFAULT_EXPIRES_IN,
        ]);
    }

    /**
     * Download document. Confidential documents require a verified email confirmation code first
     * (see requestAccessCode), obtained via Redis-backed EmailConfirmationService.
     */
    public function download(Request $request, Document $document, EmailConfirmationService $confirmation)
    {
        $this->authorize('download', $document);

        if ($document->isConfidential()) {
            $code = $request->query('code');

            if (!$code) {
                return response()->json([
                    'message' => 'This document is confidential. Request a confirmation code via POST /documents/{document}/access-code, then retry with ?code=...',
                ], 428);
            }

            $confirmed = $confirmation->verify($request->user()->email, $code);

            if (!$confirmed || ($confirmed['document_id'] ?? null) !== $document->id) {
                return response()->json([
                    'message' => 'Invalid or expired confirmation code.',
                ], 422);
            }
        }

        try {
            return $this->documentService->download($document);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to download document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get document preview URL
     */
    public function preview(Document $document): JsonResponse
    {
        $this->authorize('view', $document);

        try {
            $url = $this->documentService->getPreviewUrl($document);
            return response()->json(['url' => $url]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to get preview',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update document (new version)
     */
    public function update(Request $request, Document $document): JsonResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|max:10240',
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

            return response()->api([
                'message' => 'Document updated successfully',
                'document' => new DocumentResource($updatedDocument),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete document
     */
    public function destroy(Document $document): JsonResponse
    {
        try {
            $this->documentService->delete($document);

            return response()->api([
                'message' => 'Document deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate internship agreement PDF from template
     */
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

            return response()->api([
                'message' => 'Internship agreement generated successfully',
                'document' => new DocumentResource($document),
                'download_url' => "/api/documents/{$document->id}/download",
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Upload document template (DOCX)
     */
    public function uploadTemplate(Request $request): JsonResponse
    {
        $this->authorize('create', Document::class);

        $validated = $request->validate([
            'file' => 'required|file|mimes:docx|max:10240',
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

            return response()->api([
                'message' => 'Template uploaded successfully',
                'document' => new DocumentResource($document),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to upload template',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Generate document from uploaded template
     */
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
                return response()->json([
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

            return response()->api([
                'message' => 'Document generated successfully',
                'document' => new DocumentResource($document),
                'download_url' => "/api/documents/{$document->id}/download",
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to generate document',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
