<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Service\ActivityService;
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



    public function __construct(){
        $this->service = new ActivityService();
        $this->visitorService=new VisitorService();
        $this->officerService=new OfficerService();

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
}


