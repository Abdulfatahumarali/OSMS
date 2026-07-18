<?php

namespace Tests\Unit;

use App\Models\Application;
use App\Models\Scholarship;
use App\Services\EligibilityEvaluator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\CreatesApplication;
use Tests\TestCase;

/**
 * FR-11 to FR-13 — automated eligibility evaluation logic.
 */
class EligibilityEvaluatorTest extends TestCase
{
    use CreatesApplication, RefreshDatabase;

    public function test_application_meeting_all_criteria_is_marked_eligible(): void
    {
        $scholarship = Scholarship::factory()->create([
            'min_gpa' => 3.0,
            'programme_of_study' => 'Information Technology',
            'min_year_of_study' => 2,
            'nationality' => null,
            'requires_financial_need' => false,
        ]);

        $application = Application::factory()->create([
            'scholarship_id' => $scholarship->id,
            'programme_of_study' => 'Information Technology',
            'year_of_study' => 3,
            'gpa_submitted' => 3.5,
        ]);

        $check = (new EligibilityEvaluator())->evaluate($application);

        $this->assertSame('eligible', $check->result);
        $this->assertNull($check->failed_criteria);
        $this->assertSame('eligible', $application->fresh()->status);
        $this->assertSame(1, $application->fresh()->current_stage_order);
    }

    public function test_application_below_minimum_gpa_is_marked_ineligible_and_logged(): void
    {
        $scholarship = Scholarship::factory()->create(['min_gpa' => 3.5]);

        $application = Application::factory()->create([
            'scholarship_id' => $scholarship->id,
            'gpa_submitted' => 2.9,
            'programme_of_study' => $scholarship->programme_of_study,
            'year_of_study' => $scholarship->min_year_of_study,
        ]);

        $check = (new EligibilityEvaluator())->evaluate($application);

        $this->assertSame('ineligible', $check->result);
        $this->assertArrayHasKey('min_gpa', $check->failed_criteria);
        $this->assertSame('ineligible', $application->fresh()->status);
    }

    public function test_application_failing_multiple_criteria_logs_each_one(): void
    {
        $scholarship = Scholarship::factory()->create([
            'min_gpa' => 3.5,
            'programme_of_study' => 'Computer Science',
            'nationality' => 'Ghanaian',
            'requires_financial_need' => true,
        ]);

        $application = Application::factory()->create([
            'scholarship_id' => $scholarship->id,
            'gpa_submitted' => 2.0,
            'programme_of_study' => 'Fine Art',
            'nationality' => 'Kenyan',
            'financial_need_declared' => false,
        ]);

        $check = (new EligibilityEvaluator())->evaluate($application);

        $this->assertSame('ineligible', $check->result);
        $this->assertArrayHasKey('min_gpa', $check->failed_criteria);
        $this->assertArrayHasKey('programme_of_study', $check->failed_criteria);
        $this->assertArrayHasKey('nationality', $check->failed_criteria);
        $this->assertArrayHasKey('financial_need', $check->failed_criteria);
    }

    public function test_re_evaluating_an_application_updates_the_existing_check_record(): void
    {
        $scholarship = Scholarship::factory()->create(['min_gpa' => 3.0]);
        $application = Application::factory()->create([
            'scholarship_id' => $scholarship->id,
            'gpa_submitted' => 2.0,
        ]);

        $evaluator = new EligibilityEvaluator();
        $evaluator->evaluate($application);

        $application->update(['gpa_submitted' => 3.8]);
        $evaluator->evaluate($application->fresh('scholarship'));

        $this->assertSame(1, $application->eligibilityCheck()->count());
        $this->assertSame('eligible', $application->fresh()->eligibilityCheck->result);
    }
}
