<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Officer;
use App\Models\Visitor;
use App\Service\AppointmentService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class AppointmentController extends Controller
{
   protected AppointmentService $service;
    public function __construct(){
        $this->service=new AppointmentService();
    }
    public function index():View{
        return view('Appointment.index');
    }

    /**
     * @throws Exception
     */
    public function getAppointment():JsonResponse{
        $query = Appointment::get();
        return DataTables::of($query)
            ->addColumn('officer_name', function ($appointment) {
                return $appointment->officer->name ?? '-';
            })
            ->addColumn('visitor_name', function ($appointment) {
                return $appointment->visitor->name ?? '-';
            })
            ->addIndexColumn()
            ->make(true);
    }

    public function create():View{
        $officers=Officer::where('status','active')->orderBy('name')->get();
        $visitors=Visitor::where('status','active')->orderBy('name')->get();
        return view('Appointment.create',compact('officers','visitors'));
    }

    public function store(Request $request):JsonResponse{
        $request->validate([
            'officer_id'=>'required|exists:officers,id',
            'visitor_id'=>'required|exists:visitors,id',
            'date'=>'required|date',
            'start_time'=>'required|date_format:H:i:s',
            'end_time'=>'required|date_format:H:i:s|after:start_time'
        ]);

        try{
            Appointment::create([
                'officer_id'=>$request->officer_id,
                'visitor_id'=>$request->visitor_id,
                'appointment_date'=>$request->date,
                'start_time'=>$request->start_time,
                'end_time'=>$request->end_time,
            ]);
            return response()->json([
                'status'=>'success',
                'message'=>'Appointment Created Successfully'
            ]);

        }catch (Exception $e){
            return response()->json([
                'status'=>'error',
                'message'=>$e->getMessage()
            ]);
        }
    }
  public function cancelAppointment(int $id):JsonResponse{
        if($this->service->cancelAppointment($id)){
             return response()->json([
                 'status'=>'success',
                 'message'=>'Appointment Cancelled Successfully'
             ]);
         }else{
             return response()->json([
                 'status'=>'error',
                 'message'=>'Appointment Cancel Failed'
             ]);
         }
  }
  public function edit(int $id):View{
        $appointment=Appointment::findOrFail($id);
        $officers=Officer::where('status','active')->orderBy('name')->get();
        $visitors=Visitor::where('status','active')->orderBy('name')->get();
        return view('Appointment.update',compact('appointment','officers','visitors'));
  }
  public function update(int $id,Request $request):JsonResponse{
        $appointment=Appointment::findOrFail($id);
      $request->validate([
          'officer_id'=>'required|exists:officers,id',
          'visitor_id'=>'required|exists:visitors,id',
          'date'=>'required|date',
          'start_time'=>'required|date_format:H:i:s',
          'end_time'=>'required|date_format:H:i:s|after:start_time'
      ]);

      try{
          $appointment->update([
              'officer_id'=>$request->officer_id,
              'visitor_id'=>$request->visitor_id,
              'appointment_date'=>$request->date,
              'start_time'=>$request->start_time,
              'end_time'=>$request->end_time,
          ]);
          return response()->json([
              'status'=>'success',
              'message'=>'Appointment Updated Successfully'
          ]);

      }catch (Exception $e){
          return response()->json([
              'status'=>'error',
              'message'=>$e->getMessage()
          ]);
      }

  }
}
