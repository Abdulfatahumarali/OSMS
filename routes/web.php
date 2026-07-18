<?php

use App\Http\Controllers\Admin\EligibilityOverrideController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ScholarshipController;
use App\Http\Controllers\WorkflowController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| OSMS Web Routes
|--------------------------------------------------------------------------
| Routes are grouped by implementation status. See README.md /
| Deliverable 3 report for the full FR-to-route traceability matrix.
*/

// --- Guest routes -----------------------------------------------------
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [RegisteredUserController::class, 'store']);
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store']);
});

Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
    ->middleware('auth')
    ->name('logout');

// --- Authenticated routes ----------------------------------------------
Route::middleware('auth')->group(function () {

    // FR-03: scholarship browsing (all authenticated roles).
    Route::get('scholarships', [ScholarshipController::class, 'index'])->name('scholarships.index');
    Route::get('scholarships/{scholarship}', [ScholarshipController::class, 'show'])->name('scholarships.show');

    // FR-04 to FR-09: application submission (applicants only).
    Route::middleware('role:applicant')->group(function () {
        Route::get('scholarships/{scholarship}/apply', [ApplicationController::class, 'create'])->name('applications.create');
        Route::post('applications', [ApplicationController::class, 'store'])->name('applications.store');

        // FR-24 to FR-27: document upload (applicant uploads to their own application).
        Route::post('applications/{application}/documents', [DocumentController::class, 'store'])->name('documents.store');
    });

    Route::get('applications/{application}', [ApplicationController::class, 'show'])->name('applications.show');
    Route::get('documents/{document}/download', [DocumentController::class, 'download'])->name('documents.download');

    // --- Reviewer / Admin routes ---------------------------------------
    Route::middleware('role:reviewer,admin')->group(function () {
        // FR-16 to FR-20: approval workflow decisions.
        Route::post('applications/{application}/decision', [WorkflowController::class, 'decide'])->name('applications.decide');

        // FR-29: document verification status.
        Route::patch('documents/{document}/status', [DocumentController::class, 'updateStatus'])->name('documents.updateStatus');
    });

    // --- Admin-only routes ------------------------------------------------
    Route::middleware('role:admin')->group(function () {
        // FR-14: eligibility override.
        Route::patch('applications/{application}/eligibility', [EligibilityOverrideController::class, 'update'])
            ->name('applications.eligibility.override');

        // FR-33 to FR-50: Disbursements & Reports — NOT YET IMPLEMENTED.
        // Routes intentionally omitted until Admin\DisbursementController
        // and Admin\ReportController are built in Capstone Part 2.
    });
});

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'scholarships.index' : 'login');
});
