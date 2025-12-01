<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title') | Appointment System</title>
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    {{-- DataTables --}}
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">


    <link rel="stylesheet" href="{{ asset('css/dashboard.css') }}">
    <style>
        .sidebar a {
            display: block;
            padding: 10px 20px;
            color: #333;
            text-decoration: none;
            border-left: 4px solid transparent;
            transition: all 0.3s;
        }

        .sidebar a:hover {
            background-color: #f0f0f0;
            color: #0d6efd;
        }

        .sidebar a.active {
            background-color: #e7f1ff;
            color: #0d6efd;
            border-left-color: #0d6efd;
            font-weight: 500;
        }
    </style>

</head>

<body>

<div class="d-flex">

    {{-- SIDEBAR --}}
    <aside class="sidebar">
        <div class="p-4 border-bottom">
            <h4 class="text-primary fw-bold">Appointment System</h4>
        </div>


        <nav class="mt-3">
            <a href="{{ route('dashboard') }}" class="{{ str_contains(Route::currentRouteName(), 'dashboard') ? 'active' : '' }}">Dashboard</a>
            <a href="{{ route('post.index') }}" class="{{ str_contains(Route::currentRouteName(), 'post.') ? 'active' : '' }}">Posts</a>
            <a href="{{ route('visitor.index') }}" class="{{ str_contains(Route::currentRouteName(), 'visitor.') ? 'active' : '' }}">Visitors</a>
            <a href="{{ route('officer.index') }}" class="{{ str_contains(Route::currentRouteName(), 'officer.') ? 'active' : '' }}">Officers</a>
            <a href="{{ route('appointment.index') }}" class="{{ str_contains(Route::currentRouteName(), 'appointment.') ? 'active' : '' }}">Appointment</a>
            <a href="{{ route('activity.index') }}" class="{{ str_contains(Route::currentRouteName(), 'activity.') ? 'active' : '' }}">Activities</a>
        </nav>
    </aside>

    {{-- MAIN CONTENT --}}
    <main class="main-content">

        {{-- TOP NAV --}}
        <header class="top-nav p-3 d-flex justify-content-between align-items-center">
            <h5 class="fw-semibold mb-0">@yield('page-title')</h5>

            <span class="text-secondary">
                Welcome, Puskar!
             </span>
        </header>

        <div class="p-4">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif
            @yield('content')
        </div>

    </main>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>



<script>
    $(document).ready(function() {

        // Global DataTable defaults
        $.fn.dataTable.ext.errMode = 'none';

        window.initDataTable = function(selector, ajaxUrl, columns, extraOptions = {}) {

            let defaultOptions = {
                processing: true,
                serverSide: true,
                lengthChange: false,
                pageLength: 10,
                responsive: true,
                language: {
                    paginate: { previous: "&laquo;", next: "&raquo;" }
                },
                dom: 'lrtip',
                ajax: {
                    url: ajaxUrl,
                    type: "GET"
                },
                columns: columns
            };

            // Merge default + page-specific options
            let finalOptions = Object.assign({}, defaultOptions, extraOptions);

            let table = $(selector).DataTable(finalOptions);

            // Global search with debounce
            let searchDelay = null;
            $("#dataTableSearch").on("keyup", function () {
                clearTimeout(searchDelay);
                let value = $(this).val();

                searchDelay = setTimeout(() => {
                    table.search(value).draw();
                }, 500);
            });

            return table;
        };

    });
</script>
@yield('scripts')


</body>
</html>
