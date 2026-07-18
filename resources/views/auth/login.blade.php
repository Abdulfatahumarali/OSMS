@extends('layouts.app')
@section('title', 'Login')
@section('content')
<h1>Login (FR-02)</h1>
<form method="POST" action="{{ route('login') }}">
    @csrf
    <label>Email <input type="email" name="email" value="{{ old('email') }}" required></label><br>
    <label>Password <input type="password" name="password" required></label><br>
    <label><input type="checkbox" name="remember"> Remember me</label><br>
    <button type="submit">Login</button>
</form>
@endsection
