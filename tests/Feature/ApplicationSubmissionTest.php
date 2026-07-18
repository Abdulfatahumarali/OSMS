<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesApplication;
use Tests\TestCase;

/**
 * FR-04 to FR-09 — Application Submission feature.
 */
class ApplicationSubmissionTest extends TestCase
{
    use CreatesApplication, RefreshDatabase;

    private function validPayload(Scholarship $scholarship, array $overrides = []): array
    {
        return array_merge([
            'scholarship_id' => $scholarship->id,
            'programme_of_study' => 'Information Technology',
            'year_of_study' => 3,
            'nationality' => 'Nigerian',
            'gpa_submitted' => 3.6,
            'financial_need_declared' => 1,
            'personal_statement' => 'A sufficiently detailed personal statement.',
            'referee_name' => 'Dr. Smith',
            'referee_email' => 'smith@example.com',
        ], $overrides);
    }

    public function test_applicant_can_save_an_incomplete_draft(): void
    {
        // FR-07: draft save does not require every mandatory field.
        $applicant = User::factory()->create();
        $scholarship = Scholarship::factory()->create();

        $response = $this->actingAs($applicant)->post('/applications', [
            'scholarship_id' => $scholarship->id,
            'submit' => '0',
        ]);

        $response->assertSessionDoesntHaveErrors();
        $this->assertDatabaseHas('applications', [
            'user_id' => $applicant->id,
            'scholarship_id' => $scholarship->id,
            'status' => 'draft',
        ]);
    }

    public function test_final_submission_requires_all_mandatory_fields(): void
    {
        // FR-06: mandatory field validation on submission.
        $applicant = User::factory()->create();
        $scholarship = Scholarship::factory()->create();

        $response = $this->actingAs($applicant)->post('/applications', [
            'scholarship_id' => $scholarship->id,
            'submit' => '1',
            // personal_statement, referee_name, etc. intentionally omitted
        ]);

        $response->assertSessionHasErrors(['programme_of_study', 'personal_statement', 'referee_name']);
    }

    public function test_valid_submission_generates_reference_number_and_timestamp(): void
    {
        // FR-08, FR-09
        $applicant = User::factory()->create();
        $scholarship = Scholarship::factory()->create();

        $response = $this->actingAs($applicant)->post(
            '/applications',
            $this->validPayload($scholarship, ['submit' => '1'])
        );

        $application = Application::firstWhere('user_id', $applicant->id);

        $response->assertRedirect(route('applications.show', $application));
        $this->assertNotNull($application->reference_no);
        $this->assertMatchesRegularExpression('/^OSMS-\d{4}-[A-Z0-9]{6}$/', $application->reference_no);
        $this->assertNotNull($application->submitted_at);
        // FR-11: eligibility is evaluated immediately on submission, so the
        // final status reflects the eligibility outcome (see EligibilityValidationTest).
        $this->assertSame('eligible', $application->fresh()->status);
    }

    public function test_applicant_cannot_submit_a_second_application_for_the_same_scholarship(): void
    {
        // FR-05 / BR-01: one application per applicant per scholarship.
        $applicant = User::factory()->create();
        $scholarship = Scholarship::factory()->create();

        Application::factory()->submitted()->create([
            'user_id' => $applicant->id,
            'scholarship_id' => $scholarship->id,
        ]);

        $response = $this->actingAs($applicant)->post(
            '/applications',
            $this->validPayload($scholarship, ['submit' => '1'])
        );

        $response->assertSessionHasErrors('scholarship_id');
        $this->assertSame(1, Application::where('user_id', $applicant->id)
            ->where('scholarship_id', $scholarship->id)->count());
    }

    public function test_draft_can_later_be_completed_and_submitted(): void
    {
        // FR-07 -> FR-09 lifecycle: draft, then completed submission re-using the same record.
        $applicant = User::factory()->create();
        $scholarship = Scholarship::factory()->create();

        $this->actingAs($applicant)->post('/applications', [
            'scholarship_id' => $scholarship->id,
            'submit' => '0',
        ]);

        $this->actingAs($applicant)->post(
            '/applications',
            $this->validPayload($scholarship, ['submit' => '1'])
        );

        $this->assertSame(1, Application::where('user_id', $applicant->id)->count());
        // FR-09/FR-11: submission is recorded and immediately evaluated for eligibility.
        $application = Application::firstWhere('user_id', $applicant->id);
        $this->assertNotNull($application->submitted_at);
        $this->assertContains($application->status, ['eligible', 'ineligible']);
    }

    public function test_reviewer_cannot_submit_an_application(): void
    {
        // Authorisation: only applicants may submit applications.
        $reviewer = User::factory()->reviewer()->create();
        $scholarship = Scholarship::factory()->create();

        $response = $this->actingAs($reviewer)->post(
            '/applications',
            $this->validPayload($scholarship, ['submit' => '1'])
        );

        $response->assertForbidden();
    }
}
