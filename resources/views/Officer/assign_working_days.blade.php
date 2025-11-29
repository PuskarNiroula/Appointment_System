@extends('Layout.layout')

@section('page-title', 'Assign Working Days')

@section('content')

<div class="container mt-4">

    <div class="card shadow-sm">
        <div class="card-header">
            <h4>Assign Working Days — {{ $officer->name }}</h4>
        </div>

        <div class="card-body">


            @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{route('officer.saveWorkingDays',$officer->id)}}" method="POST">
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
                                       {{ in_array($day,$existingDays) ? 'checked' : '' }}
                                       id="day_{{ $day }}">
                                <label class="form-check-label" for="day_{{ $day }}">
                                    {{ ucfirst($day) }}
                                </label>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <button class="btn btn-primary">Save Working Days</button>
            </form>

        </div>
    </div>

</div>

@endsection
