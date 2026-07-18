<?php

namespace App\Services;

use App\Models\Application;
use App\Models\EligibilityCheck;

/**
 * FR-10 to FR-13 — Eligibility Validation.
 *
 * Evaluates a submitted application against the eligibility criteria
 * configured on its parent scholarship (minimum GPA, programme of study,
 * year of study, nationality, and financial need indicator), records the
 * result, and logs which specific criteria failed.
 */
class EligibilityEvaluator
{
    /**
     * @return EligibilityCheck the persisted evaluation record.
     */
    public function evaluate(Application $application): EligibilityCheck
    {
        $scholarship = $application->scholarship;
        $failed = [];

        if (! is_null($scholarship->min_gpa)) {
            $passed = ! is_null($application->gpa_submitted)
                && $application->gpa_submitted >= $scholarship->min_gpa;
            if (! $passed) {
                $failed['min_gpa'] = false;
            }
        }

        if (! empty($scholarship->programme_of_study)) {
            $passed = strcasecmp((string) $application->programme_of_study, $scholarship->programme_of_study) === 0;
            if (! $passed) {
                $failed['programme_of_study'] = false;
            }
        }

        if (! is_null($scholarship->min_year_of_study)) {
            $passed = ! is_null($application->year_of_study)
                && $application->year_of_study >= $scholarship->min_year_of_study;
            if (! $passed) {
                $failed['min_year_of_study'] = false;
            }
        }

        if (! empty($scholarship->nationality)) {
            $passed = strcasecmp((string) $application->nationality, $scholarship->nationality) === 0;
            if (! $passed) {
                $failed['nationality'] = false;
            }
        }

        if ($scholarship->requires_financial_need) {
            if (! $application->financial_need_declared) {
                $failed['financial_need'] = false;
            }
        }

        $result = empty($failed) ? 'eligible' : 'ineligible';

        $check = EligibilityCheck::updateOrCreate(
            ['application_id' => $application->id],
            [
                'result' => $result,
                'failed_criteria' => empty($failed) ? null : $failed,
                'evaluated_at' => now(),
            ]
        );

        $application->status = $result;
        if ($result === 'eligible') {
            // BR-03: only eligible applications enter the approval workflow,
            // starting at stage 1 (FR-16/FR-17).
            $application->current_stage_order = 1;
        }
        $application->save();

        return $check;
    }
}
