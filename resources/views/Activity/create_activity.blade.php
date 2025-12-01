@extends('Layout.layout')

@section("page-title")
    Create Activity
@endsection

@section('content')
    <div class="d-flex justify-content-center mt-5">
        <div class="card shadow-sm w-75 custom-grey-card mb-4">

            {{-- Header --}}
            <div class="card-header grey-header">
                <h5 class="mb-0 grey-title">Create New Activity</h5>
            </div>

            <div class="card-body">
                <form>

                    {{-- Activity Type --}}
                    <div class="mb-3">
                        <label for="type" class="form-label grey-label">Activity Type</label>
                        <select name="type" id="type" class="form-select modern-input @error('type') is-invalid @enderror" required>
                            <option value="">-- Select Type --</option>
                            <option value="break" {{ old('type') == 'break' ? 'selected' : '' }}>Break</option>
                            <option value="leave" {{ old('type') == 'leave' ? 'selected' : '' }}>Leave</option>
                        </select>
                        <div class="invalid-feedback">@error('type') {{ $message }} @enderror</div>
                    </div>

                    {{-- Officer --}}
                    <div class="mb-3">
                        <label for="officer_id" class="form-label grey-label">Officer</label>
                        <select name="officer_id" id="officer_id" class="form-select modern-input @error('officer_id') is-invalid @enderror" required>
                            <option value="">-- Select Officer --</option>
                            @foreach($officers as $officer)
                                <option value="{{ $officer->id }}" {{ old('officer_id') == $officer->id ? 'selected' : '' }}>
                                    {{ $officer->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">@error('officer_id') {{ $message }} @enderror</div>
                    </div>

                    {{-- Start Date --}}
                    <div class="mb-3">
                        <label for="start_date" class="form-label grey-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date"
                               class="form-control modern-input @error('start_date') is-invalid @enderror"
                               value="{{ old('start_date') }}" required>
                        <div class="invalid-feedback">@error('start_date') {{ $message }} @enderror</div>
                    </div>

                    {{-- End Date --}}
                    <div class="mb-3">
                        <label for="end_date" class="form-label grey-label">End Date</label>
                        <input type="date" name="end_date" id="end_date"
                               class="form-control modern-input @error('end_date') is-invalid @enderror"
                               value="{{ old('end_date') }}" required>
                        <div class="invalid-feedback">@error('end_date') {{ $message }} @enderror</div>
                    </div>

                    {{-- Start Time --}}
                    <div class="mb-3">
                        <label for="start_time" class="form-label grey-label">Start Time</label>
                        <input type="time" name="start_time" id="start_time"
                               class="form-control modern-input @error('start_time') is-invalid @enderror"
                               value="{{ old('start_time') }}">
                        <div class="invalid-feedback">@error('start_time') {{ $message }} @enderror</div>
                    </div>

                    {{-- End Time --}}
                    <div class="mb-3">
                        <label for="end_time" class="form-label grey-label">End Time</label>
                        <input type="time" name="end_time" id="end_time"
                               class="form-control modern-input @error('end_time') is-invalid @enderror"
                               value="{{ old('end_time') }}">
                        <div class="invalid-feedback">@error('end_time') {{ $message }} @enderror</div>
                    </div>

                    {{-- Buttons --}}
                    <button type="submit" class="btn btn-primary px-4">{{ $buttonText ?? 'Create Activity' }}</button>
                    <a href="{{ route('activities.index') }}" class="btn btn-secondary px-4">Back</a>

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
                        url: "{{route('activity.store')}}",
                        method: "POST",
                        headers: {
                            "X-CSRF-TOKEN": csrf_token
                        },
                        data: {
                            type: $('#type').val(),
                            officer_id: $('#officer_id').val(),
                            start_date: $('#start_date').val(),
                            end_date: $('#end_date').val(),
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


