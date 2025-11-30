@extends('Layout.layout')

@section("page-title")
    Update Officer
@endsection

@section('content')
    <div class="d-flex justify-content-center mt-5">
        <div class="card shadow-sm mb-4" style="width: 60%; min-width: 300px;">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Update Officer</h5>
            </div>

            <div class="card-body">
                <form >
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{$officer->name}}"
                            placeholder="Enter name" required/>
                        <label for="post_id" class="form-label">Post</label>
                        @if(!empty($posts) && $posts->count())
                            <select name="post_id" id="post_id" class="form-control" required>
                                @foreach($posts as $post)
                                    <option value="{{ $post->id }}"
                                        {{ $officer->post_id == $post->id ? 'selected' : '' }}>
                                        {{ $post->name }}
                                    </option>
                                @endforeach
                            </select>
                        @else
                            <span class="error text-danger">No posts found. Please create an active post first.</span>
                        @endif


                        <label for="start_time" class="form-label">Work Start Time</label>
                        <input
                            type="time"
                            name="start_time"
                            id="start_time"
                            class="form-control @error('start_time') is-invalid @enderror"
                            value="{{\Carbon\Carbon::parse($officer->work_start_time)->format('H:i')}}"
                            placeholder="Enter email" required/>
                        <span class="error text-danger"></span>
                        <label for="end_time" class="form-label">Work End Time</label>
                        <input
                            type="time"
                            name="end_time"
                            id="end_time"
                            class="form-control @error('end_time') is-invalid @enderror"
                            value="{{\Carbon\Carbon::parse($officer->work_end_time)->format('H:i')}}"
                            placeholder="Enter email" required/>
                        <span class="error text-danger"></span>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        {{ $buttonText ?? 'Submit' }}
                    </button>
                </form>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script>
        let errors=null;
        let csrfToken = `{{csrf_token()}}`;
        $('form').submit(function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, submit it!'
            }).then((result) => {
                if(result.isConfirmed){
                    $.ajax({
                        url: "{{ route('officer.update',$officer->id) }}",
                        type: "PUT",
                        headers: {
                            "X-CSRF-TOKEN": csrfToken
                        },
                        data: {
                            name: $('#name').val(),
                            post_id:$('#post_id').val(),
                            start_time:$('#start_time').val(),
                            end_time:$('#end_time').val(),
                        },
                        success: function (response) {
                            if(response.status === 'success'){
                                Swal.fire(
                                    'Success!',
                                    response.message||"Officer registered successfully",
                                    'success'
                                ).then((result) => {
                                    window.location.href = "{{ route('officers.index') }}";
                                });
                            }else{
                                Swal.fire(
                                    'Error!',
                                    response.message||"Something went wrong",
                                    'error'
                                );
                            }

                        },
                        error: function (xhr) {
                            let message = "Something went wrong";

                            if (xhr.responseJSON && xhr.responseJSON.errors) {
                                const errors = xhr.responseJSON.errors;
                                message = Object.values(errors).flat().join("<br>");
                            }

                            Swal.fire({
                                title: 'Error!',
                                html: message,
                                icon: 'error'
                            });
                        }

                    });
                }
            });

        });
    </script>


@endsection
