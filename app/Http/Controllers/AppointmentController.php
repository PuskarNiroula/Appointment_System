<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Officer;
use App\Models\Visitor;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class AppointmentController extends Controller
{
    public function index():View{
        return view('Appointment.index');
    }

    /**
     * @throws Exception
     */
    public function getAppointment():JsonResponse{
        return DataTables::of(Appointment::all())->make(true);
    }

    public function create():View{
        $officers=Officer::where('status','active')->get();
        $visitors=Visitor::where('status','active')->get();
        return view('Appointment.create',compact('officers','visitors'));
    }
}
