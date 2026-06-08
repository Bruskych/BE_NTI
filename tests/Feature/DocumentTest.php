<?php

namespace Tests\Feature;

use App\Mail\DocumentAccessConfirmation;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DocumentTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
    }

    public function test_user_can_list_documents()
    {
        Document::factory()->count(3)->create(['uploaded_by' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson('/api/documents');

        $response->assertStatus(200);
        $response->assertJsonCount(3);
        $response->assertJsonStructure([['id', 'file_name', 'classification']]);
    }

    public function test_user_can_upload_document()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('test.pdf', 100);

        $response = $this->actingAs($this->user)
            ->postJson('/api/documents', [
                'file' => $file,
                'type' => 'agreement',
                'classification' => 'internal',
            ]);

        $response->assertStatus(201);
        $response->assertJsonStructure(['message', 'document']);
        $this->assertDatabaseHas('documents', ['type' => 'agreement']);
    }

    public function test_uploading_document_with_disallowed_extension_is_rejected()
    {
        Storage::fake('public');
        $file = UploadedFile::fake()->create('script.exe', 100);

        // Spec 13: "Antivírusová alebo aspoň MIME / príponová kontrola uploadovaných príloh"
        $response = $this->actingAs($this->user)
            ->postJson('/api/documents', [
                'file' => $file,
                'type' => 'agreement',
                'classification' => 'internal',
            ]);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['file']);
    }

    public function test_user_can_view_own_document()
    {
        $document = Document::factory()->create(['uploaded_by' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/documents/{$document->id}");

        $response->assertStatus(200);
        $response->assertJsonPath('id', $document->id);
    }

    public function test_user_cannot_view_others_confidential_document()
    {
        $owner = User::factory()->create();
        $document = Document::factory()->create([
            'uploaded_by' => $owner->id,
            'classification' => Document::CLASSIFICATION_CONFIDENTIAL,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/documents/{$document->id}");

        $response->assertStatus(403);
    }

    public function test_user_can_download_own_document()
    {
        Storage::fake('public');
        Storage::disk('public')->put('documents/test.pdf', 'fake-pdf-contents');

        $document = Document::factory()->create([
            'uploaded_by' => $this->user->id,
            'classification' => Document::CLASSIFICATION_INTERNAL,
            'file_path' => 'documents/test.pdf',
        ]);

        $response = $this->actingAs($this->user)
            ->get("/api/documents/{$document->id}/download");

        $response->assertStatus(200);
    }

    public function test_downloading_confidential_document_requires_email_confirmation_code()
    {
        $document = Document::factory()->create([
            'uploaded_by' => $this->user->id,
            'classification' => Document::CLASSIFICATION_CONFIDENTIAL,
        ]);

        $response = $this->actingAs($this->user)
            ->getJson("/api/documents/{$document->id}/download");

        $response->assertStatus(428);
    }

    public function test_user_can_request_access_code_for_confidential_document()
    {
        Mail::fake();

        $document = Document::factory()->create([
            'uploaded_by' => $this->user->id,
            'classification' => Document::CLASSIFICATION_CONFIDENTIAL,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/documents/{$document->id}/access-code");

        $response->assertStatus(200);
        Mail::assertSent(DocumentAccessConfirmation::class, fn ($mail) => $mail->hasTo($this->user->email));
    }

    public function test_access_code_is_not_required_for_non_confidential_document()
    {
        $document = Document::factory()->create([
            'uploaded_by' => $this->user->id,
            'classification' => Document::CLASSIFICATION_INTERNAL,
        ]);

        $response = $this->actingAs($this->user)
            ->postJson("/api/documents/{$document->id}/access-code");

        $response->assertStatus(422);
    }

    public function test_user_can_delete_own_document()
    {
        $document = Document::factory()->create(['uploaded_by' => $this->user->id]);

        $response = $this->actingAs($this->user)
            ->deleteJson("/api/documents/{$document->id}");

        $response->assertStatus(200);
        $this->assertSoftDeleted('documents', ['id' => $document->id]);
    }
}
