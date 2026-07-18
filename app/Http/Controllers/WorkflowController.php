<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\ReviewDecision;
use App\Models\WorkflowStage;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

/**
 * FR-16 to FR-20 — Approval Workflow feature. Manages the structured,
 * sequential, multi-stage review of eligible applications.
 *
 * NOT YET IMPLEMENTED for this feature: FR-21 (next-stage reviewer
 * notification), FR-22 (applicant-facing status timeline UI), FR-23
 * (full decision-history view for admins). The underlying data
 * (review_decisions table) already supports these; only the
 * presentation/notification layer remains — planned for Capstone Part 2.
 */
class WorkflowController extends Controller
{
    /**
     * Records a reviewer's decision at the application's current stage
     * and advances (or closes) the application accordingly.
     *
     * FR-17: enforces sequential progression — a decision can only be
     * recorded for the stage the application is currently sitting at.
     * FR-18/RBAC: only the reviewer assigned to that stage (by user or
     * role) may record a decision.
     * FR-19: every decision is recorded with reviewer, timestamp, and
     * optional comments (audit trail).
     * FR-20: decision must be one of approve / reject / return_for_revision.
     */
    public function decide(Request $request, Application $application): RedirectResponse
    {
        $validated = $request->validate([
            'decision' => ['required', 'in:approve,reject,return_for_revision'],
            'comments' => ['nullable', 'string', 'max:2000'],
        ]);

        abort_unless($application->status === 'eligible' || $application->status === 'in_review', 422,
            'Only eligible applications currently in review may receive a decision.');

        $stage = WorkflowStage::where('scholarship_id', $application->scholarship_id)
            ->where('stage_order', $application->current_stage_order ?? 1)
            ->firstOrFail();

        $reviewer = Auth::user();
        $this->authoriseReviewer($reviewer, $stage);

        ReviewDecision::create([
            'application_id' => $application->id,
            'workflow_stage_id' => $stage->id,
            'reviewer_id' => $reviewer->id,
            'decision' => $validated['decision'],
            'comments' => $validated['comments'] ?? null,
            'decided_at' => now(),
        ]);

        $this->applyDecision($application, $stage, $validated['decision']);

        return redirect()
            ->route('applications.show', $application)
            ->with('status', 'Decision recorded.');
    }

    private function authoriseReviewer($reviewer, WorkflowStage $stage): void
    {
        $allowed = $reviewer->isAdmin()
            || $stage->assigned_user_id === $reviewer->id
            || ($stage->assigned_role && $reviewer->role === $stage->assigned_role);

        abort_unless($allowed, 403, 'You are not assigned to this workflow stage.');
    }

    private function applyDecision(Application $application, WorkflowStage $currentStage, string $decision): void
    {
        if ($decision === 'reject') {
            $application->update(['status' => 'rejected']);

            return;
        }

        if ($decision === 'return_for_revision') {
            $application->update(['status' => 'returned_for_revision']);

            return;
        }

        // approve: advance to next stage, or finalise if this was the last stage.
        $nextStage = WorkflowStage::where('scholarship_id', $application->scholarship_id)
            ->where('stage_order', $currentStage->stage_order + 1)
            ->first();

        if ($nextStage) {
            $application->update([
                'status' => 'in_review',
                'current_stage_order' => $nextStage->stage_order,
            ]);
        } else {
            $application->update(['status' => 'approved']);
        }
    }
}
