<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * FR-14 — allows an administrator to manually override an eligibility
 * determination, provided a documented justification (audit trail).
 * Route is protected by the RoleMiddleware ('admin').
 */
class EligibilityOverrideController extends Controller
{
    public function update(Request $request, Application $application): RedirectResponse
    {
        $validated = $request->validate([
            'result' => ['required', 'in:eligible,ineligible'],
            'justification' => ['required', 'string', 'min:10', 'max:2000'],
        ]);

        $check = $application->eligibilityCheck ?? $application->eligibilityCheck()->create([]);

        $check->update([
            'result' => $validated['result'],
            'is_overridden' => true,
            'overridden_by' => Auth::id(),
            'override_justification' => $validated['justification'],
            'evaluated_at' => now(),
        ]);

        $application->update(['status' => $validated['result']]);

        return redirect()
            ->route('applications.show', $application)
            ->with('status', 'Eligibility determination overridden.');
    }
}
