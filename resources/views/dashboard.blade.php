@extends('Layout.layout')

@section('page-title')
   Dashboard
@endsection

@section('content')

    <div class="container-fluid">
        <div class="row g-4 mt-3">

            <!-- Card 1 -->
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm custom-grey-card">
                    <div class="card-body">
                        <h5 class="card-title">Active Appointments</h5>
                        <h3 class="card-text">{{ $active_appointments }}</h3>
                    </div>
                </div>
            </div>

            <!-- Card 2 -->
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm custom-grey-card">
                    <div class="card-body">
                        <h5 class="card-title">Cancelled Appointments</h5>
                        <h3 class="card-text">{{ $cancelled_appointments }}</h3>
                    </div>
                </div>
            </div>

            <!-- Card 3 -->
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm custom-grey-card">
                    <div class="card-body">
                        <h5 class="card-title">Active Officers</h5>
                        <h3 class="card-text">{{ $active_officers }}</h3>
                    </div>
                </div>
            </div>

            <!-- Card 4 -->
            <div class="col-md-3 col-sm-6">
                <div class="card shadow-sm custom-grey-card">
                    <div class="card-body">
                        <h5 class="card-title">Inactive Visitors</h5>
                        <h3 class="card-text">{{ $inactive_visitors }}</h3>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <!-- FLEX TABLE SECTION -->
    <div class="container-fluid mt-5">
        <div class="flex-container">

            <!-- Upcoming Activities -->
            <div class="table-box">
                <h4>Upcoming Activities</h4>

                <table>
                    <thead>
                    <tr>
                        <th>Officer</th>
                        <th>Type</th>
                        <th>Date</th>
                        <th>Time</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($upcoming_activities as $activity)
                        <tr>
                            <td>{{ $activity->officer->name }}</td>
                            <td>{{ ucfirst($activity->type) }}</td>
                            <td>
                                @if($activity->start_date == $activity->end_date)
                                    {{ $activity->start_date }}
                                @else
                                    {{ $activity->start_date }} <br/> {{ $activity->end_date }}
                                @endif
                            </td>
                            <td>{{ $activity->start_time }} <br/> {{ $activity->end_time }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Recent Appointments -->
            <div class="table-box">
                <h4>Recent Appointments</h4>

                <table>
                    <thead>
                    <tr>
                        <th>User</th>
                        <th>Phone</th>
                        <th>Date</th>
                        <th>Time</th>
                    </tr>
                    </thead>

                    <tbody>
                    @foreach($recent_appointments as $appointment)
                        <tr>
                            <td>{{ $appointment->visitor->name }}</td>
                            <td>{{ $appointment->visitor->mobile_num }}</td>
                            <td>{{ $appointment->appointment_date }}</td>
                            <td>{{ $appointment->start_time }} <br/>{{ $appointment->end_time }}</td>
                        </tr>
                    @endforeach
                    </tbody>

                </table>
            </div>

        </div>
    </div>

@endsection
