@extends('layouts.app')
@section('title', 'Apply — ' . $scholarship->name)
@section('content')
<h1>Application Form — {{ $scholarship->name }} (FR-04)</h1>
<form method="POST" action="{{ route('applications.store') }}">
    @csrf
    <input type="hidden" name="scholarship_id" value="{{ $scholarship->id }}">

    <label>Programme of Study <input type="text" name="programme_of_study" value="{{ old('programme_of_study') }}"></label><br>
    <label>Year of Study <input type="number" name="year_of_study" value="{{ old('year_of_study') }}"></label><br>
    <label>Nationality <input type="text" name="nationality" value="{{ old('nationality') }}"></label><br>
    <label>GPA <input type="number" step="0.01" name="gpa_submitted" value="{{ old('gpa_submitted') }}"></label><br>
    <label><input type="checkbox" name="financial_need_declared" value="1"> I declare financial need</label><br>
    <label>Personal Statement<br>
        <textarea name="personal_statement" rows="6" cols="60">{{ old('personal_statement') }}</textarea>
    </label><br>
    <label>Referee Name <input type="text" name="referee_name" value="{{ old('referee_name') }}"></label><br>
    <label>Referee Email <input type="email" name="referee_email" value="{{ old('referee_email') }}"></label><br>

    <button type="submit" name="submit" value="0">Save Draft (FR-07)</button>
    <button type="submit" name="submit" value="1">Submit Application (FR-06/FR-09)</button>
</form>
@endsection
