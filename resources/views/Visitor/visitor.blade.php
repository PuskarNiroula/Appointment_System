@extends('Layout.layout')

@section("page-title", "Visitors")

@section('content')

    <div class="d-flex justify-content-between align-items-center pt-3 pb-2 mb-3 border-bottom">
        <a href="{{ route('visitor.create') }}" class="btn btn-primary">Add New Visitor</a>
    </div>

    <div class="d-flex justify-content-center mt-4">
        <div class="card shadow-sm w-100 custom-grey-card">

            {{-- Header --}}
            <div class="card-header" style="background:#f0f0f0; border-radius:12px 12px 0 0;">
                <h5 class="mb-0" style="color:#444; font-weight:600;">Visitors Table</h5>
            </div>

            {{-- Search Bar --}}
            <div class="d-flex justify-content-end mt-3 px-3">
                <div class="input-group search-box">
                    <span class="input-group-text bg-white border-end-0">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input type="text" id="dataTableSearch" class="form-control border-start-0" placeholder="Search...">
                </div>
            </div>

            {{-- Table --}}
            <div class="card-body">
                <table class="table modern-table data-table">
                    <thead>
                    <tr>
                        <th>#</th>
                        <th>Name</th>
                        <th>Mobile Number</th>
                        <th>Email</th>
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
                        url: `/visitor/${id}/deactivate/`,
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
                        url: `/visitor/${id}/activate/`,
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
                {data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false},
                {data: 'name', name: 'name'},
                {data: "mobile_num", name: "mobile_num"},
                {data: "email", name: "email"},
                {data: 'status', name: 'status'},
                {
                    data: 'status',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {

                        let editBtn = `<a href="/visitor/${row.id}/edit" class="btn btn-sm btn-primary me-1">Edit</a>`;

                        let statusBtn = row.status === 'active'
                            ? `<button class="btn btn-sm btn-warning" onclick="deactivate(${row.id})">Deactivate</button>`
                            : `<button class="btn btn-sm btn-success" onclick="activate(${row.id})">Activate</button>`;

                        return editBtn + statusBtn;
                    }
                }
            ];

            initDataTable(".data-table", "{{ route('visitors.api') }}", columns);

        });
    </script>


@endsection
