@extends('Layout.layout')

@section("page-title")
    Register New Officer
@endsection

@section('content')

    <div class="d-flex justify-content-center mt-5">
        <div class="card shadow-sm w-75 custom-grey-card mb-4">

            {{-- Header --}}
            <div class="card-header grey-header">
                <h5 class="mb-0 grey-title">Register New Officer</h5>
            </div>

            <div class="card-body">
                <form>

                    {{-- Name --}}
                    <div class="mb-3">
                        <label for="name" class="form-label grey-label">Name</label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="form-control modern-input @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            placeholder="Enter name"
                            required/>
                        <div class="invalid-feedback">
                            @error('name') {{ $message }} @enderror
                        </div>
                    </div>

                    {{-- Post --}}
                    <div class="mb-3">
                        <label for="post_id" class="form-label grey-label">Post</label>
                        @if(!empty($posts) && $posts->count())
                            <select name="post_id" id="post_id" class="form-control modern-input" required>
                                @foreach($posts as $name=>$id)
                                    <option value="{{ $id }}">{{ $name }}</option>
                                @endforeach
                            </select>
                        @else
                            <span class="error text-danger">No active posts found. Please create one first.</span>
                        @endif
                    </div>

                    {{-- Work Start Time --}}
                    <div class="mb-3">
                        <label for="start_time" class="form-label grey-label">Work Start Time</label>
                        <input
                            type="time"
                            name="start_time"
                            id="start_time"
                            class="form-control modern-input @error('start_time') is-invalid @enderror"
                            required/>
                    </div>

                    {{-- Work End Time --}}
                    <div class="mb-3">
                        <label for="end_time" class="form-label grey-label">Work End Time</label>
                        <input
                            type="time"
                            name="end_time"
                            id="end_time"
                            class="form-control modern-input @error('end_time') is-invalid @enderror"
                            required/>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" class="btn btn-primary px-4">
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
                        url: "{{ route('officer.store') }}",
                        type: "POST",
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
                                    window.location.href = "{{ route('officer.index') }}";
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
