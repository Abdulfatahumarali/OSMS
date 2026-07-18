@extends('layouts.app')
@section('title', 'Application ' . ($application->reference_no ?? '#' . $application->id))
@section('content')
<h1>Application {{ $application->reference_no ?? '(draft, no reference number yet)' }}</h1>
<p>Scholarship: {{ $application->scholarship->name }}</p>
<p>Status: <strong>{{ $application->status }}</strong></p>
<p>Submitted at: {{ optional($application->submitted_at)->toDayDateTimeString() ?? 'Not yet submitted' }}</p>

@if ($application->eligibilityCheck)
    <h2>Eligibility (FR-12)</h2>
    <p>Result: {{ $application->eligibilityCheck->result }}</p>
    @if ($application->eligibilityCheck->failed_criteria)
        <p>Failed criteria (FR-13):</p>
        <ul>
            @foreach (array_keys($application->eligibilityCheck->failed_criteria) as $criterion)
                <li>{{ $criterion }}</li>
            @endforeach
        </ul>
    @endif
@endif

<h2>Documents (FR-24 to FR-29)</h2>
<ul>
    @foreach ($application->documents as $document)
        <li>{{ $document->original_filename }} — {{ $document->verification_status }}</li>
    @endforeach
</ul>

@if (auth()->user()->isApplicant() && $application->status === 'draft')
    <form method="POST" action="{{ route('documents.store', $application) }}" enctype="multipart/form-data">
        @csrf
        <input type="file" name="documents[]" multiple>
        <button type="submit">Upload Document(s)</button>
    </form>
@endif
@endsection
