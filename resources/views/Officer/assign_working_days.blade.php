@extends('Layout.layout')

@section('page-title', 'Assign Working Days')

@section('content')

    <div class="d-flex justify-content-center mt-5">
        <div class="card shadow-sm w-75">

            {{-- Header --}}
            <div class="card-header bg-primary text-white">
                <h5 class="mb-0">Assign Working Days — {{ $officer->name }}</h5>
            </div>

            <div class="card-body">

                {{-- Error Messages --}}
                @if($errors->any())
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Form --}}
                <form action="{{ route('officer.saveWorkingDays', $officer->id) }}" method="POST">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label">Working Days</label>
                        <div class="row">
                            @foreach($days as $day)
                                <div class="col-md-3">
                                    <div class="form-check">
                                        <input class="form-check-input"
                                               type="checkbox"
                                               name="days[]"
                                               value="{{ $day }}"
                                               {{ in_array($day, $existingDays) ? 'checked' : '' }}
                                               id="day_{{ $day }}">
                                        <label class="form-check-label" for="day_{{ $day }}">
                                            {{ ucfirst($day) }}
                                        </label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary px-4">Save Working Days</button>
                </form>

            </div>
        </div>
    </div>

@endsection
