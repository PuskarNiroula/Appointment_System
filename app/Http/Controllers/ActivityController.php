<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Appointment;
use App\Service\ActivityService;
use App\Service\AppointmentService;
use App\Service\OfficerService;
use App\Service\VisitorService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class ActivityController extends Controller
{
    protected ActivityService $service;
    protected VisitorService $visitorService;
    protected OfficerService $officerService;
    protected AppointmentService $appointmentService;



    public function __construct(){
        $this->service = new ActivityService();
        $this->visitorService=new VisitorService();
        $this->officerService=new OfficerService();
        $this->appointmentService=new AppointmentService();

    }
    public function index(): View
    {
        return view('Activity.activities');
    }

    /**
     * @throws Exception
     */

    public function getActivities(): JsonResponse
    {
        $query = Activity::get(); // eager load officer

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('officer_name', function ($activity) {
                return $activity->officer->name ?? '-';
            })
            ->make(true);
    }

    public function create(): View
    {
        $officers = $this->officerService->getWorkingOfficers();
        return view('Activity.create_activity', compact('officers'));
    }

    public function store(Request $request): JsonResponse
    {
        // All the validation remains here
        $request->validate([
            'officer_id' => 'required|exists:officers,id',
            'type' => ['required', 'string', 'in:leave,break'],
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'start_time' => 'required|date_format:H:i:s',
            'end_time' => ['required', 'date_format:H:i:s', function ($attribute, $value, $fail) use ($request) {
                if ($request->start_date == $request->end_date) {
                    $start = Carbon::createFromFormat('H:i:s', $request->start_time);
                    $end = Carbon::createFromFormat('H:i:s', $value);
                    if ($end->lte($start)) {
                        $fail('The end time must be after start time when start date and end date are the same.');
                    }
                }
            }],
        ]);
        //checking for pastData
        $now=Carbon::now('Asia/Kathmandu');

        if($request->start_date<$now->toDateString()){
            return response()->json([
                'status'=>'error',
                'message'=>'Activity Date Must Be In Future'
            ]);
        }
        if($request->start_date==$now->toDateString()){
            if($request->start_time<$now->toTimeString()){
                return response()->json([
                    'status'=>'error',
                    'message'=>"Activity Time Must Be In Future",
                ]);
            }
        }

        // converting to array for sending to service
        $data = [
            'officer_id' => $request->officer_id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ];

        // Passing the clean data to service
        $response = $this->service->store($data);

        return response()->json($response);
    }

    public function edit(int $id){
        $activity=Activity::findOrFail($id);
        $officers=$this->officerService->getWorkingOfficers();
        return view('Activity.update_activity',compact('activity','officers'));

    }


public function update(int $id, Request $request):JsonResponse
{
    $request->validate([
        'officer_id' => 'required|exists:officers,id',
        'type' => ['required', 'string', 'in:leave,break'],
        'start_date' => 'required|date',
        'end_date' => 'required|date|after_or_equal:start_date',
        'start_time' => 'required|date_format:H:i:s',
        'end_time' => ['required', 'date_format:H:i:s', function ($attribute, $value, $fail) use ($request) {
            if ($request->start_date == $request->end_date) {
                $start = Carbon::createFromFormat('H:i:s', $request->start_time);
                $end = Carbon::createFromFormat('H:i:s', $value);
                if ($end->lte($start)) {
                    $fail('The end time must be after start time when start date and end date are the same.');
                }
            }
        }],
    ]);

    $myActivity=Activity::findOrFail($id);
   if(!$this->service->checkWorkingDay($id,$request->start_date)){
       return response()->json([
           'status'=>'error',
           'message'=>'Officer do not work on this day'
       ]);
   }
   $rep=$this->service->checkWorkingHour($id,$request->start_time,$request->end_time);
    if($rep['status']=='error')
       return response()->json($rep);


    $now = Carbon::now('Asia/Kathmandu');

    $activities = Activity::where('officer_id', $request->officer_id)
        ->whereNotIn('status', ['cancelled', 'completed'])
        ->where(function ($query) use ($now) {
            $query->where('end_date', '>', $now->toDateString())
                ->orWhere(function ($q) use ($now) {
                    $q->where('end_date', $now->toDateString())
                        ->where('end_time', '>=', $now->toTimeString());
                });
        })
        ->where('id', '!=', $id)
        ->get();


    //condition

    foreach($activities as $activity){

        //single-day checks
        if($request->start_date==$request->end_date){
           if(!$this->service->singleDayCheck($request->start_date,$request->start_time,$request->end_time,$activity)){
               return response()->json([
                   'status'=>'error',
                   'message'=>'Officer is busy in this time slot on single day'
               ]);
           }


        }else{
            if(!$this->service->multiDayCheck($request->start_date,$request->end_date,$request->start_time,$request->end_time,$activity)){
                return response()->json([
                    'status'=>'error',
                    'message'=>'Officer is busy in this time slot on multi day'
                ]);
            }
        }
    }
    $myActivity->update([
        'officer_id' => $request->officer_id,
        'type' => $request->type,
        'start_date' => $request->start_date,
        'end_date' => $request->end_date,
        'start_time' => $request->start_time,
        'end_time' => $request->end_time,
    ]);
return response()->json([
    'status'=>'success',
    'message'=>'Activity Updated Successfully'
]);
}

public function cancel(int $id){
        $activity=Activity::findOrFail($id);

        try{
            if($activity->type=='appointment') {
                $appointment_id = Appointment::where('officer_id', $activity->officer_id)
                    ->where('appointment_date', $activity->start_date)
                    ->whereNotIn('status', ['cancelled', 'completed'])
                    ->where('appointment_date', '>=', $activity->end_date)
                    ->where('start_time', '<=', $activity->start_time)
                    ->where('end_time', '>=', $activity->end_time)
                    ->first()->id;

                if ($this->appointmentService->cancelAppointment($appointment_id)) {
                    return response()->json([
                        'status' => 'success',
                        'message' => 'Activity Cancelled Successfully'
                    ]);
                } else {
                    return response()->json([
                        'status' => 'error',
                        'message' => 'Activity Cancel Failed'
                    ]);
                }
            }else{
                $activity->update(['status'=>'cancelled']);
            }

        }catch (Exception $e){
            return response()->json([
                'status'=>'error',
                'message'=>$e->getMessage()
            ]);
        }
}


}


