<?php

namespace Tests\Feature;

use App\Models\Application;
use App\Models\Scholarship;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesApplication;
use Tests\TestCase;

/**
 * FR-14 — administrator override of an eligibility determination.
 */
class EligibilityValidationTest extends TestCase
{
    use CreatesApplication, RefreshDatabase;

    public function test_admin_can_override_an_ineligible_determination_with_justification(): void
    {
        $admin = User::factory()->admin()->create();
        $scholarship = Scholarship::factory()->create();
        $application = Application::factory()->create([
            'scholarship_id' => $scholarship->id,
            'status' => 'ineligible',
        ]);
        $application->eligibilityCheck()->create([
            'result' => 'ineligible',
            'failed_criteria' => ['min_gpa' => false],
            'evaluated_at' => now(),
        ]);

        $response = $this->actingAs($admin)->patch("/applications/{$application->id}/eligibility", [
            'result' => 'eligible',
            'justification' => 'Departmental waiver approved by the Dean per memo #2026-014.',
        ]);

        $response->assertRedirect(route('applications.show', $application));
        $this->assertSame('eligible', $application->fresh()->status);
        $this->assertTrue($application->fresh()->eligibilityCheck->is_overridden);
        $this->assertSame($admin->id, $application->fresh()->eligibilityCheck->overridden_by);
    }

    public function test_override_requires_a_justification(): void
    {
        $admin = User::factory()->admin()->create();
        $application = Application::factory()->create(['status' => 'ineligible']);

        $response = $this->actingAs($admin)->patch("/applications/{$application->id}/eligibility", [
            'result' => 'eligible',
            'justification' => 'too short',
        ]);

        $response->assertSessionHasErrors('justification');
    }

    public function test_non_admin_cannot_override_eligibility(): void
    {
        $reviewer = User::factory()->reviewer()->create();
        $application = Application::factory()->create(['status' => 'ineligible']);

        $response = $this->actingAs($reviewer)->patch("/applications/{$application->id}/eligibility", [
            'result' => 'eligible',
            'justification' => 'Attempting an unauthorised override of this decision.',
        ]);

        $response->assertForbidden();
    }
}
