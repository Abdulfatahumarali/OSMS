<?php

namespace App\Http\Controllers;

use App\Models\Scholarship;

/**
 * FR-03 — displays all open scholarships with name, description,
 * eligibility summary, award value, and deadline.
 */
class ScholarshipController extends Controller
{
    public function index()
    {
        $scholarships = Scholarship::where('is_published', true)
            ->orderBy('closes_at')
            ->get();

        return view('scholarships.index', compact('scholarships'));
    }

    public function show(Scholarship $scholarship)
    {
        return view('scholarships.show', compact('scholarship'));
    }
}
