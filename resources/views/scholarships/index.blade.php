@extends('layouts.app')
@section('title', 'Scholarships')
@section('content')
<h1>Open Scholarships (FR-03)</h1>
<ul>
    @foreach ($scholarships as $scholarship)
        <li>
            <a href="{{ route('scholarships.show', $scholarship) }}">{{ $scholarship->name }}</a>
            — Award: {{ number_format($scholarship->award_value, 2) }}
            — Deadline: {{ $scholarship->closes_at->toFormattedDateString() }}
        </li>
    @endforeach
</ul>
@endsection
