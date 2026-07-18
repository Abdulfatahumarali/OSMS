<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadDocumentRequest;
use App\Models\Application;
use App\Models\Document;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

/**
 * FR-24 to FR-29 — Document Upload and Verification.
 *
 * NOT YET IMPLEMENTED for this feature: FR-30 (structured rejection
 * reason UI beyond the raw field), FR-31 (email notification on
 * rejection), FR-32 (guided re-upload flow). The `rejection_reason`
 * column and re-upload via a fresh `store()` call already exist at the
 * data layer; the applicant-facing workflow is planned for Capstone Part 2.
 */
class DocumentController extends Controller
{
    /**
     * FR-24: multiple documents per application.
     * FR-25/FR-26: format and size validated by UploadDocumentRequest.
     * FR-27: stored securely (private disk) and associated with the application.
     */
    public function store(UploadDocumentRequest $request, Application $application): RedirectResponse
    {
        abort_unless(Auth::id() === $application->user_id, 403);

        foreach ($request->file('documents') as $file) {
            $path = $file->store('documents/'.$application->id, 'local');

            Document::create([
                'application_id' => $application->id,
                'original_filename' => $file->getClientOriginalName(),
                'stored_path' => $path,
                'mime_type' => $file->getClientMimeType(),
                'size_bytes' => $file->getSize(),
                'verification_status' => 'pending',
            ]);
        }

        return redirect()
            ->route('applications.show', $application)
            ->with('status', 'Document(s) uploaded.');
    }

    /**
     * FR-28: administrators and reviewers may view uploaded documents.
     */
    public function download(Document $document)
    {
        $user = Auth::user();
        abort_unless($user->isAdmin() || $user->isReviewer() || $document->application->user_id === $user->id, 403);

        return Storage::disk('local')->download($document->stored_path, $document->original_filename);
    }

    /**
     * FR-29: administrators set verification status to Verified, Rejected, or Pending.
     */
    public function updateStatus(Request $request, Document $document): RedirectResponse
    {
        $validated = $request->validate([
            'verification_status' => ['required', 'in:pending,verified,rejected'],
            'rejection_reason' => ['required_if:verification_status,rejected', 'nullable', 'string', 'max:1000'],
        ]);

        $document->update([
            'verification_status' => $validated['verification_status'],
            'rejection_reason' => $validated['verification_status'] === 'rejected' ? ($validated['rejection_reason'] ?? null) : null,
            'verified_by' => Auth::id(),
            'verified_at' => now(),
        ]);

        return redirect()
            ->route('applications.show', $document->application)
            ->with('status', 'Document verification status updated.');
    }
}
