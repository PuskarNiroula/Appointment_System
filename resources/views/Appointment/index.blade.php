@extends('Layout.layout')

@section("page-title", "Appointments")

@section('content')

    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <a href="{{ route('appointment.create') }}" class="btn btn-primary">Create Appointment</a>
    </div>

    <div class="d-flex justify-content-center mt-4">
        <div class="card shadow-sm w-75">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Appointments Table</h5>
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
                        <th>Officer Name</th>
                        <th>Visitor Name</th>
                        <th>Date</th>
                        <th>Start Time</th>
                        <th>End Time</th>
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
        function cancel(id) {
            Swal.fire({
                "title": "Are you sure?",
                showCancelButton: true,
            }).then((result) => {
                if(result.isConfirmed){
                    $.ajax({
                        url: `/appointment/${id}/cancel/`,
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
                {data: 'officer_name', name: 'officer_name'},
                {data: 'visitor_name', name: 'visitor_name'},
                {data: 'appointment_date', name: 'appointment_date'},
                {data: 'start_time', name: 'start_time'},
                {data: 'end_time', name: 'end_time'},
                {data: 'status', name: 'status', orderable: false, searchable: false},
                {
                    data: 'status',
                    name: 'action',
                    orderable: false,
                    searchable: false,
                    render: function(data, type, row) {
                        if(row.status==="cancelled") return ("Cancelled")
                        if(row.status==="completed") return ("Completed")

                        let editBtn = `<a href="/appointment/${row.id}/edit" class="btn btn-sm btn-primary me-1">Edit</a>`;

                        let statusBtn = row.status === 'active'
                            ? `<button class="btn btn-sm btn-warning" onclick="cancel(${row.id})">Cancel</button>`
                            : 'deactivated';

                        return editBtn + statusBtn;
                    }
                }
            ];
            initDataTable(".data-table", "{{ route('appointment.api') }}", columns);
        });
    </script>


@endsection
