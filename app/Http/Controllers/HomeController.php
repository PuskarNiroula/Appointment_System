<?php

namespace App\Http\Controllers;


use App\Models\Activity;
use App\Models\Appointment;
use App\Models\Officer;
use App\Models\Visitor;

class HomeController extends Controller
{

    public function dashboard(){
        $active_appointments=Appointment::where('status','active')->count();
        $cancelled_appointments=Appointment::where('status','cancelled')->count();
        $active_officers=Officer::where('status','active')->count();
        $inactive_visitors=Visitor::where('status','inactive')->count();
        $upcoming_activities=Activity::where('status','active')
            ->where(
                'start_date','>=',now('Asia/Kathmandu')->toDateString()
            )
            ->orderBy('start_date')
            ->limit(10)
            ->get();
        $recent_appointments=Appointment::where('status','completed')
            ->limit(10)
            ->orderBy('appointment_date','desc')
            ->get();
        return view('dashboard',compact([
            'active_appointments',
            'cancelled_appointments',
            'active_officers',
            'inactive_visitors',
            'upcoming_activities',
            'recent_appointments'
        ]));
    }

}
