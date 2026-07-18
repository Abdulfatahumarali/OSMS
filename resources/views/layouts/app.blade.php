<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>@yield('title', 'OSMS') — Online Scholarship Management System</title>
</head>
<body>
    <nav>
        <a href="{{ route('scholarships.index') }}">Scholarships</a>
        @auth
            | {{ auth()->user()->name }} ({{ auth()->user()->role }})
            <form method="POST" action="{{ route('logout') }}" style="display:inline">
                @csrf
                <button type="submit">Logout</button>
            </form>
        @else
            | <a href="{{ route('login') }}">Login</a>
            | <a href="{{ route('register') }}">Register</a>
        @endauth
    </nav>
    <hr>

    @if (session('status'))
        <div class="alert">{{ session('status') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <main>
        @yield('content')
    </main>
</body>
</html>
