<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Scholarship;
use App\Models\User;
use App\Models\WorkflowStage;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesApplication;
use Tests\TestCase;

/**
 * FR-16 to FR-20 — Approval Workflow feature.
 */
class ApprovalWorkflowTest extends TestCase
{
    use CreatesApplication, RefreshDatabase;

    private function eligibleApplicationWithTwoStages(): array
    {
        $scholarship = Scholarship::factory()->create();
        $screener = User::factory()->reviewer()->create();
        $finalApprover = User::factory()->admin()->create();

        $stage1 = WorkflowStage::factory()->create([
            'scholarship_id' => $scholarship->id, 'name' => 'Screening',
            'stage_order' => 1, 'assigned_user_id' => $screener->id, 'assigned_role' => null,
        ]);
        $stage2 = WorkflowStage::factory()->create([
            'scholarship_id' => $scholarship->id, 'name' => 'Final Approval',
            'stage_order' => 2, 'assigned_user_id' => $finalApprover->id, 'assigned_role' => null,
        ]);

        $application = Application::factory()->create([
            'scholarship_id' => $scholarship->id,
            'status' => 'eligible',
            'current_stage_order' => 1,
        ]);

        return compact('application', 'stage1', 'stage2', 'screener', 'finalApprover');
    }

    public function test_assigned_reviewer_can_approve_and_application_advances_to_next_stage(): void
    {
        ['application' => $application, 'screener' => $screener] = $this->eligibleApplicationWithTwoStages();

        $response = $this->actingAs($screener)->post("/applications/{$application->id}/decision", [
            'decision' => 'approve',
        ]);

        $response->assertRedirect(route('applications.show', $application));
        $application->refresh();
        $this->assertSame('in_review', $application->status);
        $this->assertSame(2, $application->current_stage_order);
        $this->assertDatabaseHas('review_decisions', [
            'application_id' => $application->id,
            'reviewer_id' => $screener->id,
            'decision' => 'approve',
        ]);
    }

    public function test_approval_at_final_stage_marks_application_approved(): void
    {
        ['application' => $application, 'finalApprover' => $finalApprover] = $this->eligibleApplicationWithTwoStages();
        $application->update(['status' => 'in_review', 'current_stage_order' => 2]);

        $this->actingAs($finalApprover)->post("/applications/{$application->id}/decision", [
            'decision' => 'approve',
        ]);

        $this->assertSame('approved', $application->fresh()->status);
    }

    public function test_rejection_at_any_stage_closes_the_application(): void
    {
        ['application' => $application, 'screener' => $screener] = $this->eligibleApplicationWithTwoStages();

        $this->actingAs($screener)->post("/applications/{$application->id}/decision", [
            'decision' => 'reject',
            'comments' => 'Does not meet the programme requirement.',
        ]);

        $this->assertSame('rejected', $application->fresh()->status);
    }

    public function test_reviewer_not_assigned_to_the_current_stage_cannot_decide(): void
    {
        ['application' => $application] = $this->eligibleApplicationWithTwoStages();
        // application is at stage 1 (Screening), assigned to a different reviewer.
        $unrelatedReviewer = User::factory()->reviewer()->create();

        $response = $this->actingAs($unrelatedReviewer)->post("/applications/{$application->id}/decision", [
            'decision' => 'approve',
        ]);

        $response->assertForbidden();
    }

    public function test_applicant_cannot_record_a_review_decision(): void
    {
        ['application' => $application] = $this->eligibleApplicationWithTwoStages();
        $applicant = User::factory()->create();

        $response = $this->actingAs($applicant)->post("/applications/{$application->id}/decision", [
            'decision' => 'approve',
        ]);

        $response->assertForbidden();
    }

    public function test_decision_must_be_a_valid_option(): void
    {
        ['application' => $application, 'screener' => $screener] = $this->eligibleApplicationWithTwoStages();

        $response = $this->actingAs($screener)->post("/applications/{$application->id}/decision", [
            'decision' => 'maybe_later',
        ]);

        $response->assertSessionHasErrors('decision');
    }
}
