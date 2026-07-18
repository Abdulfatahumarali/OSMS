<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreApplicationRequest;
use App\Models\Application;
use App\Models\Scholarship;
use App\Services\EligibilityEvaluator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * FR-04 to FR-09 — Application Submission feature.
 */
class ApplicationController extends Controller
{
    public function __construct(private readonly EligibilityEvaluator $evaluator)
    {
    }

    public function create(Scholarship $scholarship)
    {
        return view('applications.create', compact('scholarship'));
    }

    /**
     * Creates a draft, updates a draft, or performs final submission
     * depending on the `submit` flag validated by StoreApplicationRequest.
     */
    public function store(StoreApplicationRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $user = Auth::user();
        $scholarshipId = $data['scholarship_id'];

        // FR-05 / BR-01: an applicant may hold only one application record
        // (draft or submitted) per scholarship.
        $application = Application::firstOrNew([
            'user_id' => $user->id,
            'scholarship_id' => $scholarshipId,
        ]);

        if ($application->exists && $application->status !== 'draft') {
            throw ValidationException::withMessages([
                'scholarship_id' => 'You have already submitted an application for this scholarship.',
            ]);
        }

        $application->fill([
            'programme_of_study' => $data['programme_of_study'] ?? $application->programme_of_study,
            'year_of_study' => $data['year_of_study'] ?? $application->year_of_study,
            'nationality' => $data['nationality'] ?? $application->nationality,
            'gpa_submitted' => $data['gpa_submitted'] ?? $application->gpa_submitted,
            'financial_need_declared' => $request->boolean('financial_need_declared'),
            'personal_statement' => $data['personal_statement'] ?? $application->personal_statement,
            'referee_name' => $data['referee_name'] ?? $application->referee_name,
            'referee_email' => $data['referee_email'] ?? $application->referee_email,
        ]);

        $isSubmit = $request->boolean('submit');

        if ($isSubmit) {
            // FR-08: unique alphanumeric reference number.
            $application->reference_no = $application->reference_no ?? $this->generateReferenceNumber();
            // FR-09: record submission date/time.
            $application->submitted_at = now();
            $application->status = 'submitted';
        } else {
            // FR-07: save as an incomplete draft.
            $application->status = 'draft';
        }

        $application->save();

        if ($isSubmit) {
            // FR-11: automatic eligibility evaluation immediately on submission.
            $this->evaluator->evaluate($application->fresh('scholarship'));
        }

        return redirect()
            ->route('applications.show', $application)
            ->with('status', $isSubmit ? 'Application submitted successfully.' : 'Draft saved.');
    }

    public function show(Application $application)
    {
        $this->authorizeView($application);

        return view('applications.show', compact('application'));
    }

    private function authorizeView(Application $application): void
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $user->isReviewer() || $application->user_id === $user->id, 403);
    }

    /**
     * FR-08: unique alphanumeric reference number, e.g. OSMS-2026-8F3K2Q.
     */
    private function generateReferenceNumber(): string
    {
        do {
            $candidate = 'OSMS-'.now()->format('Y').'-'.Str::upper(Str::random(6));
        } while (Application::where('reference_no', $candidate)->exists());

        return $candidate;
    }
}
