<?php

namespace Tests\Feature;

use App\Models\ExportsLog;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportTest extends TestCase
{
    use RefreshDatabase;

    private ?string $generatedFilePath = null;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(RoleAndPermissionSeeder::class);
    }

    protected function tearDown(): void
    {
        // GenerateExport writes directly to storage_path('app/...'), bypassing the Storage facade/disks
        if ($this->generatedFilePath && file_exists(storage_path('app/' . $this->generatedFilePath))) {
            unlink(storage_path('app/' . $this->generatedFilePath));
        }

        parent::tearDown();
    }

    private function admin(): User
    {
        $admin = User::factory()->create();
        $admin->assignRole('admin');

        return $admin;
    }

    /**
     * Spec: "exporty do CSV, XLSX a PDF / DOCX reportov podľa filtra" — DOCX must be
     * offered alongside CSV/XLSX/PDF for the report-style resources.
     */
    public function test_docx_export_type_is_offered_for_report_resources()
    {
        $this->actingAs($this->admin())
            ->getJson('/api/admin/exports/types')
            ->assertStatus(200)
            ->assertJsonFragment(['type' => 'users_docx', 'format' => 'docx'])
            ->assertJsonFragment(['type' => 'projects_docx', 'format' => 'docx'])
            ->assertJsonFragment(['type' => 'applications_docx', 'format' => 'docx']);
    }

    public function test_admin_can_generate_a_docx_export_of_users()
    {
        User::factory()->count(2)->create();

        $response = $this->actingAs($this->admin())
            ->postJson('/api/admin/export/users/docx')
            ->assertStatus(202);

        $logId = $response->json('export.id');
        $log = ExportsLog::findOrFail($logId);

        $this->assertSame('users_docx', $log->export_type);
        $this->assertNotNull($log->file_path);
        $this->assertStringEndsWith('.docx', $log->file_path);

        $this->generatedFilePath = $log->file_path;
        $this->assertFileExists(storage_path('app/' . $log->file_path));
    }
}
