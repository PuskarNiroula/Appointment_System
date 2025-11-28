<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | Appointment System</title>

    {{-- Bootstrap CSS --}}
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet">

    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">

</head>

<body>

<div class="d-flex">

    {{-- SIDEBAR --}}
    <aside class="sidebar">
        <div class="p-4 border-bottom">
            <h4 class="text-primary fw-bold">Appointment System</h4>
        </div>

        <nav class="mt-3">
            <a href="#">Dashboard</a>
            <a href="#">Posts</a>
            <a href="#">Visitors</a>
            <a href="#">Officers</a>
            <a href="#">Appointment</a>
            <a href="#">Activities</a>
        </nav>
    </aside>

    {{-- MAIN CONTENT --}}
    <main class="main-content">

        {{-- TOP NAV --}}
        <header class="top-nav p-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-semibold mb-0">@yield('page-title')</h5>

            <span class="text-secondary">
                    Hello, {{ auth()->user()->name ?? 'User' }}
                </span>
        </header>

        {{-- PAGE CONTENT --}}
        <div class="p-4">
            @yield('content')
        </div>

    </main>

</div>

{{-- Bootstrap JS --}}
<script
    src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js">
</script>

</body>
</html>
