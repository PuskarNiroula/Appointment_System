@extends('Layout.layout')

@section("page-title")
    Edit Post
@endsection

@section('content')
    <div class="d-flex justify-content-center mt-5">
        <div class="card shadow-sm custom-grey-card mb-4" style="width: 60%; min-width: 320px;">

            <!-- Header -->
            <div class="card-header" style="background:#f0f0f0; border-radius:12px 12px 0 0;">
                <h5 class="mb-0" style="color:#444; font-weight:600;">Edit Post</h5>
            </div>

            <div class="card-body">

                <form method="POST">
                    @csrf

                    <!-- Name Input -->
                    <div class="mb-3">
                        <label for="name" class="form-label" style="font-weight:600; color:#555;">Name</label>

                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="form-control modern-input @error('name') is-invalid @enderror"
                            value="{{ $post->name }}"
                        >

                        <div class="invalid-feedback">
                           <span class="error"></span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" class="btn btn-primary px-4">
                        {{ $buttonText ?? 'Submit' }}
                    </button>

                </form>
            </div>
        </div>
    </div>


    <script>
        let csrf_token = `{{csrf_token()}}`;
        $('form').submit(function (e) {
            e.preventDefault();

            let name = document.getElementById('name');
            let error = document.querySelector('.invalid-feedback .error');



            // Validation
            if (name.value.trim() === "") {
                name.classList.add("is-invalid");
                error.innerHTML = "Name is required";
                return;
            } else {
                name.classList.remove("is-invalid");
                error.innerHTML = "";
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
                        url: "/posts/{{$post->id}}/update",
                        method: "PUT",
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
                                    text: "Post updated successfully"||response.message,
                                }).then(() => {
                                    window.location.href = "{{route('post.index')}}";
                                });
                            }else{
                                Swal.fire({
                                    icon: "error",
                                    title: "error!",
                                    text: "Post cannot be saved!!!!!"||response.message,
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
