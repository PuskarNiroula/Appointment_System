@extends('Layout.layout')

@section("page-title")
    Create Post
@endsection

@section('content')
    <div class="d-flex justify-content-center mt-5">
        <div class="card shadow-sm mb-4" style="width: 60%; min-width: 300px;">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Create New Post</h5>
            </div>

            <div class="card-body">
                <form action="{{ route('post.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label for="name" class="form-label">Name</label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="form-control @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            placeholder="Enter name">
                        <div class="invalid-feedback">
                            @error('name') {{ $message }} @enderror
                        </div>
                        <span class="error text-danger"></span>
                    </div>

                    <button type="submit" class="btn btn-primary">
                        {{ $buttonText ?? 'Submit' }}
                    </button>
                </form>
            </div>
        </div>
    </div>



    <script>

        let csrf_token = `{{ csrf_token() }}`;

        $('form').submit(function (e) {
            e.preventDefault();

            let name = document.getElementById('name');
            let error = document.querySelector('.error');

            // Clear previous error
            error.innerHTML = "";

            // Validation
            if (name.value.trim() === "") {
                error.innerHTML = "Name is required";
                return;
            }

            // Confirmation Alert
            Swal.fire({
                title: "Are you sure?",
                icon: "question",
                showCancelButton: true,
                confirmButtonText: "Yes",
                cancelButtonText: "No",
            }).then((result) => {

                if (result.isConfirmed) {

                    $.ajax({
                        url: "/posts/create",
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": csrf_token
                        },
                        data: {
                            name: name.value
                        },

                        success: function (response) {
                        if(response.status === 'success'){
                            Swal.fire({
                                icon: "success",
                                title: "Saved!",
                                text: response.message||"Post saved successfully",
                            }).then(() => {
                                location.reload(); // reload page
                            });
                        }else{
                            Swal.fire({
                                icon: "error",
                                title: "error!",
                                text: response.message||"Post cannot be saved!!!!!",
                            });
                        }
                        },

                        error: function (xhr) {
                            Swal.fire({
                                icon: "error",
                                title: "Failed",
                                text:  xhr.responseJSON?.message ||"Something went wrong",
                            });
                        }

                    });
                }

            });

        });

    </script>

@endsection


