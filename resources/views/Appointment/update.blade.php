@extends('Layout.layout')

@section("page-title")
    Appointments
@endsection

@section('content')
    <div class="d-flex justify-content-center mt-5">
        <div class="card shadow-sm w-75 custom-grey-card mb-4">

            {{-- Header --}}
            <div class="card-header grey-header">
                <h5 class="mb-0 grey-title">Update Appointment</h5>
            </div>

            <div class="card-body">
                <form>

                    {{-- Officer --}}
                    <div class="mb-3">
                        <label for="officer_id" class="form-label grey-label">Officer</label>
                        <select name="officer_id" id="officer_id" class="form-select modern-input @error('officer_id') is-invalid @enderror" required>
                            <option value="">-- Select Officer --</option>
                            @foreach($officers as $officer)
                                <option value="{{ $officer->id }}" {{ $appointment->officer_id == $officer->id ? 'selected' : '' }}>
                                    {{ $officer->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">@error('officer_id') {{ $message }} @enderror</div>
                    </div>

                    {{-- Visitor --}}
                    <div class="mb-3">
                        <label for="visitor_id" class="form-label grey-label">Visitor</label>
                        <select name="visitor_id" id="visitor_id" class="form-select modern-input @error('visitor_id') is-invalid @enderror" required>
                            <option value="">-- Select Visitor --</option>
                            @foreach($visitors as $visitor)
                                <option value="{{ $visitor->id }}" {{ $appointment->visitor_id == $visitor->id ? 'selected' : '' }}>
                                    {{ $visitor->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">@error('visitor_id') {{ $message }} @enderror</div>
                    </div>

                    {{-- Date --}}
                    <div class="mb-3">
                        <label for="date" class="form-label grey-label">Date</label>
                        <input type="date" name="date" id="date"
                               class="form-control modern-input @error('date') is-invalid @enderror"
                               value="{{$appointment->appointment_date }}" required>
                        <div class="invalid-feedback">@error('date') {{ $message }} @enderror</div>
                    </div>

                    {{-- Start Time --}}
                    <div class="mb-3">
                        <label for="start_time" class="form-label grey-label">Start Time</label>
                        <input type="time" name="start_time" id="start_time"
                               class="form-control modern-input @error('start_time') is-invalid @enderror"
                               value="{{ \Carbon\Carbon::createFromFormat('H:i:s', $appointment->start_time)->format('H:i') }}">
                        <div class="invalid-feedback">@error('start_time') {{ $message }} @enderror</div>
                    </div>

                    {{-- End Time --}}
                    <div class="mb-3">
                        <label for="end_time" class="form-label grey-label">End Time</label>
                        <input type="time" name="end_time" id="end_time"
                               class="form-control modern-input @error('end_time') is-invalid @enderror"
                               value="{{ \Carbon\Carbon::createFromFormat('H:i:s', $appointment->end_time)->format('H:i') }}">
                        <div class="invalid-feedback">@error('end_time') {{ $message }} @enderror</div>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" class="btn btn-primary px-4">Update Appointment</button>

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
                        url: "{{route('appointment.update',$appointment->id)}}",
                        method: "PUT",
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


