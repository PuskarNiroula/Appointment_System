@extends('Layout.layout')

@section("page-title", "Officers")

@section('content')

    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <a href="{{ route('officer.create') }}" class="btn btn-primary">Add New Officer</a>
    </div>

    <div class="d-flex justify-content-center mt-4">
        <div class="card shadow-sm w-75">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Officers Table</h5>
            </div>
            <div class="d-flex justify-content-end mb-3">
                <div class="input-group" style="max-width: 300px; padding: 10px;">
        <span class="input-group-text bg-white border-end-0">
            <i class="bi bi-search text-muted"></i>
        </span>
                    <input type="text" id="dataTableSearch" class="form-control border-start-0" placeholder="Search...">
                </div>
            </div>




            <div class="card-body">
                <table class="table table-striped table-bordered data-table" style="width:100%">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Post</th>
                        <th>Working Time</th>
                        <th>Working Days</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let csrf_token =`{{csrf_token()}}`;
        function deactivate(id) {
            Swal.fire({
                "title": "Are you sure?",
                showCancelButton: true,
            }).then((result) => {
                if(result.isConfirmed){
                    $.ajax({
                        url: `/officer/${id}/deactivate/`,
                        method: 'patch',
                        headers: {
                            "X-CSRF-TOKEN": csrf_token
                        },

                        success: function (response) {
                            if(response.status === 'success'){
                                Swal.fire("Success!", response.message, "success").then(()=>{
                                    location.reload();
                                });
                            }else{
                                Swal.fire("Error!", response.message, "error");
                            }
                        },
                        errors: function (error) {
                            Swal.fire("Error!", "Something went wrong", "error");
                        }
                    });

                }
            })
        }
        function activate(id) {
            Swal.fire({
                "title": "Are you sure?",
                showCancelButton: true,
            }).then((result) => {
                if(result.isConfirmed){
                    $.ajax({
                        url: `/officer/${id}/activate/`,
                        method: 'patch',
                        headers: {
                            "X-CSRF-TOKEN": csrf_token
                        },

                        success: function (response) {
                            if(response.status === 'success'){
                                Swal.fire("Success!", response.message, "success").then(()=>{
                                    location.reload();
                                });

                            }else{
                                Swal.fire("Error!", response.message, "error");
                            }
                        },
                        errors: function (error) {
                            Swal.fire("Error!", "Something went wrong", "error");
                        }
                    });

                }
            })
        }

        $(document).ready(function () {

            let columns = [
                {data: 'DT_RowIndex', name: 'id',orderable:false, searchable:false},
                {data: 'name', name: 'name'},
                {
                    data: 'post',
                    name: 'post',
                },
                {data: "working_hour", name: "working_hour",
                    render: function(data, type, row) {
                        return row.work_start_time + '-' + row.work_end_time;
                    }
                },
                {
                    data: "work_day",
                    name: "work_day",
                    render: function(data, type, row) {
                        if (!data) return '-';

                        // Split the string by comma, trim spaces, capitalize first letter
                        let days = data.split(',').map(function(d) {
                            d = d.trim();
                            return d.charAt(0).toUpperCase() + d.slice(1);
                        });

                        return days.join(', ');
                    }
                },


                {data: 'status', name: 'status'},
                {
                    data: 'status',
                    name: 'action',
                    render: function (data, type, row) {

                        // Common options — always available
                        let editOption = `
            <li>
                <a class="dropdown-item" href="/officer/${row.id}/edit">Edit</a>
            </li>
        `;

                        // Activate/Deactivate
                        let statusOption = row.status === 'active'
                            ? `<li><a class="dropdown-item" href="javascript:void(0)" onclick="deactivate(${row.id})">Deactivate</a></li>`
                            : `<li><a class="dropdown-item" href="javascript:void(0)" onclick="activate(${row.id})">Activate</a></li>`;

                        // These two should only appear if ACTIVE
                        let extraOptions = `
            <li>
                <a class="dropdown-item" href="/officer/${row.id}/working-days">Assign Working Days</a>
            </li>
            <li>
                <a class="dropdown-item" href="/officer/${row.id}/appointments">View Appointments</a>
            </li>
        `;

                        return `
        <div class="dropdown">
            <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown">
                Actions
            </button>
            <ul class="dropdown-menu">

                ${editOption}
                ${statusOption}

                ${row.status === 'active' ? extraOptions : ''}

            </ul>
        </div>
        `;
                    }
                }
            ];
            initDataTable(".data-table", "{{ route('officers.api') }}", columns);
        });
    </script>


@endsection
