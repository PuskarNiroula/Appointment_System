@extends('Layout.layout')

@section("page-title")
    Register New Visitor
@endsection

@section('content')
    <div class="d-flex justify-content-center mt-5">
        <div class="card shadow-sm w-75 custom-grey-card mb-4">

            {{-- Header --}}
            <div class="card-header grey-header">
                <h5 class="mb-0 grey-title">Register New Visitor</h5>
            </div>

            {{-- Form --}}
            <div class="card-body">
                <form action="{{ route('visitor.store') }}" method="POST">
                    @csrf

                    {{-- Name --}}
                    <div class="mb-3">
                        <label for="name" class="form-label grey-label">Name</label>
                        <input
                            type="text"
                            name="name"
                            id="name"
                            class="form-control modern-input @error('name') is-invalid @enderror"
                            value="{{ old('name') }}"
                            placeholder="Enter name"/>
                        <div class="invalid-feedback">
                            @error('name') {{ $message }} @enderror
                        </div>
                    </div>

                    {{-- Mobile Number --}}
                    <div class="mb-3">
                        <label for="mobile_number" class="form-label grey-label">Mobile Number</label>
                        <input
                            type="tel"
                            name="mobile_number"
                            id="mobile_number"
                            class="form-control modern-input"
                            placeholder="Enter mobile number"/>
                    </div>

                    {{-- Email --}}
                    <div class="mb-3">
                        <label for="email" class="form-label grey-label">Email</label>
                        <input
                            type="email"
                            name="email"
                            id="email"
                            class="form-control modern-input"
                            placeholder="Enter email"/>
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
            const name = $('#name').val().trim();
            const mobile_number = $('#mobile_number').val().trim();
            const email = $('#email').val().trim();
            if (email === "" || mobile_number === "" || name === "") {
                errors = "Please fill all the fields";
                return;

            }
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
                        url: "{{ route('visitor.store') }}",
                        type: "POST",
                        headers: {
                            "X-CSRF-TOKEN": csrfToken
                        },
                        data: {
                            name: name,
                            mobile_number: mobile_number,
                            email: email,
                        },
                        success: function (response) {
                            if(response.status === 'success'){
                                Swal.fire(
                                    'Success!',
                                    response.message||"Visitor registered successfully",
                                    'success'
                                ).then((result) => {
                                    window.location.href = "{{ route('visitors.index') }}";
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
