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

</head>

<body>

<div class="d-flex">

    {{-- SIDEBAR --}}
    <aside class="sidebar">
        <div class="p-4 border-bottom">
            <h4 class="text-primary fw-bold">Appointment System</h4>
        </div>

        <nav class="mt-3">
            <a href="/dashboard">Dashboard</a>
            <a href="/posts">Posts</a>
            <a href="/visitors">Visitors</a>
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

        <div class="p-4">
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
