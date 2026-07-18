<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Document;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\CreatesApplication;
use Tests\TestCase;

/**
 * FR-24 to FR-29 — Document Upload and Verification feature.
 */
class DocumentUploadTest extends TestCase
{
    use CreatesApplication, RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_applicant_can_upload_multiple_valid_documents(): void
    {
        $applicant = User::factory()->create();
        $application = Application::factory()->create(['user_id' => $applicant->id, 'status' => 'draft']);

        $response = $this->actingAs($applicant)->post("/applications/{$application->id}/documents", [
            'documents' => [
                UploadedFile::fake()->create('transcript.pdf', 200, 'application/pdf'),
                UploadedFile::fake()->image('id_card.jpg'),
            ],
        ]);

        $response->assertRedirect(route('applications.show', $application));
        $this->assertSame(2, $application->documents()->count());
        $this->assertDatabaseHas('documents', [
            'application_id' => $application->id,
            'original_filename' => 'transcript.pdf',
            'verification_status' => 'pending',
        ]);
    }

    public function test_upload_rejects_disallowed_file_types(): void
    {
        // FR-25: only PDF/JPEG/PNG accepted.
        $applicant = User::factory()->create();
        $application = Application::factory()->create(['user_id' => $applicant->id, 'status' => 'draft']);

        $response = $this->actingAs($applicant)->post("/applications/{$application->id}/documents", [
            'documents' => [UploadedFile::fake()->create('script.exe', 100, 'application/x-msdownload')],
        ]);

        $response->assertSessionHasErrors('documents.0');
        $this->assertSame(0, $application->documents()->count());
    }

    public function test_upload_rejects_files_larger_than_5mb(): void
    {
        // FR-26: maximum 5 MB per file.
        $applicant = User::factory()->create();
        $application = Application::factory()->create(['user_id' => $applicant->id, 'status' => 'draft']);

        $response = $this->actingAs($applicant)->post("/applications/{$application->id}/documents", [
            'documents' => [UploadedFile::fake()->create('big.pdf', 6000, 'application/pdf')],
        ]);

        $response->assertSessionHasErrors('documents.0');
    }

    public function test_applicant_cannot_upload_to_another_applicants_application(): void
    {
        $owner = User::factory()->create();
        $intruder = User::factory()->create();
        $application = Application::factory()->create(['user_id' => $owner->id, 'status' => 'draft']);

        $response = $this->actingAs($intruder)->post("/applications/{$application->id}/documents", [
            'documents' => [UploadedFile::fake()->create('doc.pdf', 100, 'application/pdf')],
        ]);

        $response->assertForbidden();
    }

    public function test_reviewer_can_mark_a_document_verified(): void
    {
        // FR-29
        $reviewer = User::factory()->reviewer()->create();
        $application = Application::factory()->create();
        $document = Document::factory()->for($application)->create(['verification_status' => 'pending']);

        $response = $this->actingAs($reviewer)->patch("/documents/{$document->id}/status", [
            'verification_status' => 'verified',
        ]);

        $response->assertRedirect(route('applications.show', $application));
        $this->assertSame('verified', $document->fresh()->verification_status);
        $this->assertSame($reviewer->id, $document->fresh()->verified_by);
    }

    public function test_rejecting_a_document_requires_a_reason(): void
    {
        $reviewer = User::factory()->reviewer()->create();
        $document = Document::factory()->create();

        $response = $this->actingAs($reviewer)->patch("/documents/{$document->id}/status", [
            'verification_status' => 'rejected',
        ]);

        $response->assertSessionHasErrors('rejection_reason');
    }
}
