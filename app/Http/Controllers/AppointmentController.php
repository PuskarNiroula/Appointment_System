<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Service\ActivityService;
use App\Service\AppointmentService;
use App\Service\OfficerService;
use App\Service\VisitorService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class AppointmentController extends Controller
{
   protected AppointmentService $service;
   protected VisitorService $visitorService;
    protected ActivityService $activityService;
    protected OfficerService $officerService;
    protected AppointmentService $appointmentService;
    public function __construct(){
        $this->service=new AppointmentService();
        $this->visitorService=new VisitorService();
        $this->activityService=new ActivityService();
        $this->officerService=new OfficerService();
        $this->appointmentService=new AppointmentService();
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
                return $appointment->officer->name;
            })
            ->addColumn('visitor_name', function ($appointment) {
                return $appointment->visitor->name ;
            })
            ->addIndexColumn()
            ->make(true);
    }

    public function create():View{
        $officers=$this->officerService->getWorkingOfficers();
        $visitors=$this->visitorService->getActiveVisitors();
        return view('Appointment.create',compact('officers','visitors'));
    }

    public function store(Request $request):JsonResponse{
        $now=Carbon::now('Asia/Kathmandu');
        $request->validate([
            'officer_id'=>'required|exists:officers,id',
            'visitor_id'=>'required|exists:visitors,id',
            'date'=>'required|date',
            'start_time'=>'required|date_format:H:i:s',
            'end_time'=>'required|date_format:H:i:s|after:start_time'
        ]);


        try{
            if($request->date<$now->toDateString()){
                return response()->json([
                    'status'=>'error',
                    'message'=>'Appointment Date Must Be In Future'
                ]);
            }
            if($request->date==$now->toDateString()){
                if($request->start_time<$now->toTimeString()){
                    return response()->json([
                        'status'=>'error',
                        'message'=>'Appointment Time Must Be In Future (Your time:'.$now->toTimeString().'-'.$request->start_time.')'
                    ]);
                }
            }
            //checking for the visitor
            if(!$this->visitorService->checkIfAvailable(
                $request->visitor_id,
                $request->date,
                $request->start_time,
                $request->end_time)
            ){
                return response()->json([
                    'status'=>'error',
                    'message'=>'Visitor is not available on this time slot'
                ]);
            }
            DB::beginTransaction();
            $appointment_data=[
                'officer_id'=>$request->officer_id,
                'visitor_id'=>$request->visitor_id,
                'appointment_date'=>$request->date,
                'start_time'=>$request->start_time,
                'end_time'=>$request->end_time,
            ];

           $this->appointmentService->store($appointment_data);

            $data = [
                'officer_id' => $request->officer_id,
                'type' => 'appointment',
                'start_date' => $request->date,
                'end_date' => $request->date,
                'start_time' => $request->start_time,
                'end_time' => $request->end_time,
            ];
            $resp= $this->activityService->store($data);

            if($resp['status']=='success') {
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => 'Appointment Created Successfully'
                ]);
            }
            DB::rollBack();
            return response()->json([
                'status'=>'error',
                'message'=>$resp['message']??'Appointment Created Failed'
            ]);
        }catch (Exception $e){
            DB::rollBack();
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
        $officers=$this->officerService->getWorkingOfficers();
        $visitors=$this->visitorService->getActiveVisitors();
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
              'status'=>'active'
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
