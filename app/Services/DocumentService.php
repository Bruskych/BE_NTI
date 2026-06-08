<?php

namespace App\Services;

use App\Models\Document;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class DocumentService
{
    const STORAGE_PATH = 'documents';

    /**
     * Upload document to storage
     */
    public function upload(UploadedFile $file, array $data): Document
    {
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs(self::STORAGE_PATH, $fileName, 'public');

        return Document::create([
            'application_id'  => $data['application_id'] ?? null,
            'project_id'      => $data['project_id'] ?? null,
            'milestone_id'    => $data['milestone_id'] ?? null,
            'type'            => $data['type'] ?? 'general',
            'file_name'       => $file->getClientOriginalName(),
            'file_path'       => $filePath,
            'mime_type'       => $file->getMimeType(),
            'size'            => $file->getSize(),
            'version'         => 1,
            'classification'  => $data['classification'] ?? Document::CLASSIFICATION_INTERNAL,
            'uploaded_by'     => $data['uploaded_by'],
        ]);
    }

    /**
     * Download document
     */
    public function download(Document $document)
    {
        if (!Storage::disk('public')->exists($document->file_path)) {
            throw new \Exception("Document file not found: {$document->file_path}");
        }

        return Storage::disk('public')->download($document->file_path, $document->file_name);
    }

    /**
     * Get document preview URL
     */
    public function getPreviewUrl(Document $document): string
    {
        return asset('storage/' . $document->file_path);
    }

    /**
     * Delete document
     */
    public function delete(Document $document): bool
    {
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        return $document->delete();
    }

    /**
     * Create new version of document
     */
    public function updateVersion(UploadedFile $file, Document $document, int $uploadedBy): Document
    {
        // Delete old file
        if (Storage::disk('public')->exists($document->file_path)) {
            Storage::disk('public')->delete($document->file_path);
        }

        // Upload new file
        $fileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        $filePath = $file->storeAs(self::STORAGE_PATH, $fileName, 'public');

        $document->update([
            'file_path'   => $filePath,
            'file_name'   => $file->getClientOriginalName(),
            'mime_type'   => $file->getMimeType(),
            'size'        => $file->getSize(),
            'version'     => $document->version + 1,
            'uploaded_by' => $uploadedBy,
        ]);

        return $document;
    }

    /**
     * Generate document from template with user data
     */
    public function generateFromTemplate(string $templateName, array $data): string
    {
        // Render Blade template to HTML
        $html = view("templates.{$templateName}", $data)->render();
        
        // Store temporary file
        $fileName = Str::uuid() . '.html';
        $filePath = self::STORAGE_PATH . '/' . $fileName;
        Storage::disk('public')->put($filePath, $html);

        return $filePath;
    }

    /**
     * Generate PDF from DOCX template with student data
     */
    public function generatePdfFromDocx(string $docxPath, array $data, ?string $outputName = null): Document
    {
        // Read DOCX and prepare HTML
        $html = $this->docxToHtml($docxPath, $data);
        
        // Generate PDF from HTML
        $pdf = Pdf::loadHTML($html);
        
        // Create temporary file
        $fileName = ($outputName ?? Str::slug($data['student_name'] ?? 'document')) . '.pdf';
        $filePath = self::STORAGE_PATH . '/' . Str::uuid() . '.pdf';
        
        // Save PDF to storage
        Storage::disk('public')->put($filePath, $pdf->output());
        
        // Create document record
        return Document::create([
            'type' => 'generated',
            'file_name' => $fileName,
            'file_path' => $filePath,
            'mime_type' => 'application/pdf',
            'size' => strlen($pdf->output()),
            'version' => 1,
            'classification' => Document::CLASSIFICATION_CONFIDENTIAL,
            'uploaded_by' => $data['uploaded_by'] ?? null,
        ]);
    }

    /**
     * Generate PDF from template document by ID
     */
    public function generatePdfFromTemplate(Document $template, array $data, ?string $outputName = null): Document
    {
        if ($template->type !== 'template') {
            throw new \Exception("Document is not a template");
        }

        $docxPath = Storage::disk('public')->path($template->file_path);
        return $this->generatePdfFromDocx($docxPath, $data, $outputName);
    }

    /**
     * Convert DOCX to HTML with data substitution
     */
    private function docxToHtml(string $docxPath, array $data): string
    {
        // Read DOCX file
        if (!file_exists($docxPath)) {
            throw new \Exception("DOCX file not found: {$docxPath}");
        }

        // Extract DOCX (it's a ZIP file)
        $zip = new \ZipArchive();
        if (!$zip->open($docxPath)) {
            throw new \Exception("Failed to open DOCX file");
        }

        // Read document.xml
        $xml = $zip->getFromName('word/document.xml');
        $zip->close();

        if (!$xml) {
            throw new \Exception("Failed to read document from DOCX");
        }

        // Parse XML and convert to HTML
        $html = $this->parseDocxXml($xml, $data);
        
        return $html;
    }

    /**
     * Parse DOCX XML and replace placeholders with data
     */
    private function parseDocxXml(string $xml, array $data): string
    {
        // Load and parse XML
        $dom = new \DOMDocument();
        $dom->loadXML($xml);

        // Extract text content
        $xpath = new \DOMXPath($dom);
        $paragraphs = $xpath->query('//w:p');

        $html = '<html><body style="font-family: Arial, sans-serif; line-height: 1.6;">';
        $substituted = false;

        foreach ($paragraphs as $paragraph) {
            $text = '';
            $runs = $xpath->query('.//w:t', $paragraph);

            foreach ($runs as $run) {
                $text .= $run->nodeValue;
            }

            // Replace placeholders like {{student_name}}, {{email}}, etc.
            foreach ($data as $key => $value) {
                foreach (['{{' . $key . '}}', '${' . $key . '}'] as $placeholder) {
                    if (str_contains($text, $placeholder)) {
                        $text = str_replace($placeholder, $value ?? '', $text);
                        $substituted = true;
                    }
                }
            }

            if (trim($text)) {
                $html .= '<p>' . htmlspecialchars($text) . '</p>';
            }
        }

        // The template may not contain {{...}}/${...} placeholders at all (e.g. fixed
        // legal templates with blank lines instead of tokens). In that case nothing above
        // would carry the submitted data into the document, so append it explicitly rather
        // than silently dropping it.
        if (!$substituted) {
            $html .= $this->renderSubmittedDataTable($data);
        }

        $html .= '</body></html>';

        return $html;
    }

    /**
     * Render submitted form data as a visible table, used as a fallback when the
     * source template has no placeholder tokens to substitute values into.
     */
    private function renderSubmittedDataTable(array $data): string
    {
        $rows = '';

        foreach ($data as $key => $value) {
            if ($key === 'uploaded_by' || !filled($value)) {
                continue;
            }

            $label = ucwords(str_replace('_', ' ', $key));
            $rows .= '<tr>'
                . '<td style="padding:4px 12px;border:1px solid #ccc;font-weight:bold;">' . htmlspecialchars($label) . '</td>'
                . '<td style="padding:4px 12px;border:1px solid #ccc;">' . htmlspecialchars((string) $value) . '</td>'
                . '</tr>';
        }

        if ($rows === '') {
            return '';
        }

        return '<h3>Submitted details</h3>'
            . '<table style="border-collapse:collapse;">' . $rows . '</table>';
    }
}
