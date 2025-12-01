@extends('Layout.layout')

@section("page-title")
     Activity
@endsection

@section('content')
    <div class="d-flex justify-content-center mt-5">
        <div class="card shadow-sm mb-4" style="width: 60%; min-width: 300px;">
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Update Activity</h5>
            </div>

            <div class="card-body">
                <form>


                    {{-- Activity Type --}}
                    <div class="mb-3">
                        <label for="type" class="form-label">Activity Type</label>
                        <select name="type" id="type" class="form-select @error('type') is-invalid @enderror" required>
                            <option value="">-- Select Type --</option>
                            <option value="break" {{$activity->type == 'break' ? 'selected' : '' }}>Break</option>
                            <option value="leave" {{ $activity->type == 'leave' ? 'selected' : '' }}>Leave</option>
                        </select>
                        <div class="invalid-feedback">
                            @error('type') {{ $message }} @enderror
                        </div>
                    </div>

                    {{-- Officer --}}
                    <div class="mb-3">
                        <label for="officer_id" class="form-label">Officer</label>
                        <select name="officer_id" id="officer_id" class="form-select @error('officer_id') is-invalid @enderror" required>
                            <option value="">-- Select Officer --</option>
                            @foreach($officers as $officer)
                                <option value="{{ $officer->id }}" {{ $activity->officer_id== $officer->id ? 'selected' : '' }}>
                                    {{ $officer->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="invalid-feedback">
                            @error('officer_id') {{ $message }} @enderror
                        </div>
                    </div>

                    {{-- Start Date --}}
                    <div class="mb-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" name="start_date" id="start_date"
                               class="form-control @error('start_date') is-invalid @enderror"
                               value="{{ $activity->start_date }}" required>
                        <div class="invalid-feedback">
                            @error('start_date') {{ $message }} @enderror
                        </div>
                    </div>

                    {{-- End Date --}}
                    <div class="mb-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" name="end_date" id="end_date"
                               class="form-control @error('end_date') is-invalid @enderror"
                               value="{{ $activity->end_date }}" required>
                        <div class="invalid-feedback">
                            @error('end_date') {{ $message }} @enderror
                        </div>
                    </div>

                    {{-- Start Time --}}
                    <div class="mb-3">
                        <label for="start_time" class="form-label">Start Time</label>
                        <input type="time" name="start_time" id="start_time"
                               class="form-control @error('start_time') is-invalid @enderror"
                               value="{{\Carbon\Carbon::createFromFormat('H:i:s',$activity->start_time)->format('H:i')}}">
                        <div class="invalid-feedback">
                            @error('start_time') {{ $message }} @enderror
                        </div>
                    </div>

                    {{-- End Time --}}
                    <div class="mb-3">
                        <label for="end_time" class="form-label">End Time</label>
                        <input type="time" name="end_time" id="end_time"
                               class="form-control @error('end_time') is-invalid @enderror"
                               value="{{\Carbon\Carbon::createFromFormat('H:i:s',$activity->end_time)->format('H:i')}}">
                        <div class="invalid-feedback">
                            @error('end_time') {{ $message }} @enderror
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary">Update Activity</button>
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
                        url: "{{route('activity.update',$activity->id)}}",
                        method: "PUT",
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


