@extends('Layout.layout')

@section("page-title")
     Appointments
@endsection

@section('content')
    <div class="d-flex justify-content-center mt-5">
        <div class="card shadow-sm mb-4" style="width: 60%; min-width: 300px;">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Create New Appointment</h5>
            </div>

            <div class="card-body">
                <form>

                    {{-- Officer --}}
                    <div class="mb-3">
                        <label for="officer_id" class="form-label">Officer</label>
                        <select name="officer_id" id="officer_id" class="form-select @error('officer_id') is-invalid @enderror" required>
                            <option value="">-- Select Officer --</option>
                            @foreach($officers as $officer)
                                <option value="{{ $officer->id }}" {{ old('officer_id') == $officer->id ? 'selected' : '' }}>
                                    {{ $officer->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">
                            @error('officer_id') {{ $message }} @enderror
                        </div>
                    </div>

                    {{-- Officer --}}
                    <div class="mb-3">
                        <label for="visitor_id" class="form-label">Visitor</label>
                        <select name="visitor_id" id="visitor_id" class="form-select @error('visitor_id') is-invalid @enderror" required>
                            <option value="">-- Select Visitor --</option>
                            @foreach($visitors as $visitor)
                                <option value="{{ $visitor->id }}" {{ old('visitor_id') == $visitor->id ? 'selected' : '' }}>
                                    {{ $visitor->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>



                    {{-- Start Date --}}
                    <div class="mb-3">
                        <label for="date" class="form-label"> Date</label>
                        <input type="date" name="date" id="date"
                               class="form-control @error('date') is-invalid @enderror"
                               value="{{ old('date') }}" required>
                        <div class="invalid-feedback">
                            @error('date') {{ $message }} @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="start_time" class="form-label">Start Time</label>
                        <input type="time" name="start_time" id="start_time"
                               class="form-control @error('start_time') is-invalid @enderror"
                               value="{{ old('start_time') }}">
                        <div class="invalid-feedback">
                            @error('start_time') {{ $message }} @enderror
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="end_time" class="form-label">End Time</label>
                        <input type="time" name="end_time" id="end_time"
                               class="form-control @error('end_time') is-invalid @enderror"
                               value="{{ old('end_time') }}">
                        <div class="invalid-feedback">
                            @error('end_time') {{ $message }} @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Create Appointment</button>
                </form>
            </div>
        </div>
    </div>



    <script>

        let csrf_token = `{{ csrf_token() }}`;

        $('form').submit(function (e) {
            e.preventDefault();

            let name = document.getElementById('name');
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
                        url: "{{route('appointment.store')}}",
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": csrf_token
                        },
                        data: {

                            officer_id: $('#officer_id').val(),
                            visitor_id: $('#visitor_id').val(),
                            date: $('#date').val(),
                            start_time: $('#start_time').val()+":00",
                            end_time: $('#end_time').val()+":00",
                        },

                        success: function (response) {
                            if(response.status === 'success'){
                                Swal.fire({
                                    icon: "success",
                                    title: "Saved!",
                                    text: response.message||"activity saved successfully",
                                }).then(() => {
                                    location.reload(); // reload page
                                });
                            }else{
                                Swal.fire({
                                    icon: "error",
                                    title: "error!",
                                    text: response.message||"activity cannot be saved!!!!!",
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


