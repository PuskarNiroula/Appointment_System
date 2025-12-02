<?php

namespace App\Http\Controllers;


use App\Service\ActivityService;
use App\Service\AppointmentService;
use App\Service\OfficerService;
use App\Service\VisitorService;
use Illuminate\View\View;

class HomeController extends Controller
{
    protected AppointmentService $appointmentService;
    protected VisitorService $visitorService;
    protected OfficerService $officerService;
    protected ActivityService $activityService;
    public function __construct(){
        $this->appointmentService=app(AppointmentService::class);
        $this->visitorService=app(VisitorService::class);
        $this->officerService=app(OfficerService::class);
        $this->activityService=app(ActivityService::class);
    }

    public function dashboard():View{
        $active_appointments=$this->appointmentService->getActiveAppointmentCount();
        $cancelled_appointments=$this->appointmentService->getCanceledAppointmentCount();
        $active_officers=$this->officerService->getActiveOfficerCount();
        $inactive_visitors=$this->visitorService->getInactiveVisitorCount();
        $upcoming_activities=$this->activityService->getFutureActivities();
        $recent_appointments=$this->appointmentService->getRecentCompletedAppointments();
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
