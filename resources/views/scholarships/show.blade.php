@extends('layouts.app')
@section('title', $scholarship->name)
@section('content')
<h1>{{ $scholarship->name }}</h1>
<p>{{ $scholarship->description }}</p>
<p>Award value: {{ number_format($scholarship->award_value, 2) }}</p>
<p>Deadline: {{ $scholarship->closes_at->toFormattedDateString() }}</p>

@auth
    @if (auth()->user()->isApplicant())
        <a href="{{ route('applications.create', $scholarship) }}">Apply now</a>
    @endif
@endauth
@endsection
