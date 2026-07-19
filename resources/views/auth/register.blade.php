@extends('layouts.app')
@section('title', 'Register')
@section('content')
<h1>Register — Applicant Account (FR-01)</h1>

@if ($errors->any())
    <div style="color: red;">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('register') }}">
    @csrf
    <label>Name <input type="text" name="name" value="{{ old('name') }}" required></label><br>
    <label>Email <input type="email" name="email" value="{{ old('email') }}" required></label><br>
    <label>Password <input type="password" name="password" required></label><br>
    <label>Confirm Password <input type="password" name="password_confirmation" required></label><br>
    <button type="submit">Register</button>
</form>
@endsection
