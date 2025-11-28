@extends('Layout.layout')

@section("page-title", "Posts")

@section('content')

    <div
        class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
        <a href="{{ route('post.create') }}" class="btn btn-primary">Create Post</a>
    </div>

    <div class="d-flex justify-content-center mt-4">
        <div class="card shadow-sm w-75">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Posts Table</h5>
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
                        <th>Post Name</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    <script>
        let csrf_token =`{{csrf_token()}}`;
        function deactivate(id) {
           Swal.fire({
               "title": "Are you sure?",
               showCancelButton: true,
           }).then((result) => {
               if(result.isConfirmed){
                   $.ajax({
                       url: `/posts/${id}/deactivate/`,
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
                        url: `/posts/${id}/activate/`,
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
            let table = $('.data-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('posts.api') }}",
                    type: 'GET',
                },
                columns: [
                    {data: 'DT_RowIndex', name: 'id', orderable: false, searchable: false},
                    {data: 'name', name: 'name'},
                    {data: 'status', name: 'status'},
                    {
                        data: 'status',
                        name: 'action',
                        orderable: false,
                        searchable: false,render: function(data, type, row) {
                            // Edit button
                            let editBtn = `<a href="/posts/edit/${row.id}" class="btn btn-sm btn-primary me-1">Edit</a>`;

                            // Status button
                            let statusBtn = row.status === 'active'
                                ? `<button class="btn btn-sm btn-warning" onclick="deactivate(${row.id})">Deactivate</button>`
                                : `<button class="btn btn-sm btn-success" onclick="activate(${row.id})">Activate</button>`;

                            // Combine buttons
                            return editBtn + statusBtn;
                        }


                    }
                ],
                lengthChange: false,
                pageLength: 10,
                responsive: true,
                language: {
                    paginate: {previous: "&laquo;", next: "&raquo;"}
                },
                dom: 'lrtip'
            });

            let searchDelay = null;
            $('#dataTableSearch').on('keyup', function () {
                clearTimeout(searchDelay);
                const value = $(this).val();
                searchDelay = setTimeout(function () {
                    table.search(value).draw();
                }, 500);
            });
        });


    </script>

@endsection
