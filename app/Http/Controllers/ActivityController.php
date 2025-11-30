<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Officer;
use App\Models\WorkDay;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class ActivityController extends Controller
{
    public function index():View{
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

    public function create():View{
        $officers=Officer::all();
        return view('Activity.create_activity',compact('officers'));
    }

    public function store(Request $request): JsonResponse
    {
        // Validation
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


//        check for working days completed
        //these logic works do not touch
        $workingDays = WorkDay::where('officer_id', $request->officer_id)->pluck('day_of_week')->toArray() ?? [];
        $requestDay = strtolower(Carbon::parse($request->start_date)->format('l')) ?? [];
        if (!in_array($requestDay, $workingDays)) {
            return response()->json([
                'status' => 'error',
                'message' => "The officer does not work on {$requestDay}."
            ]);
        }
        $workingTime = Officer::where('id', $request->officer_id)
            ->select('work_start_time', 'work_end_time')
            ->first();


        $newStartDate = Carbon::parse($request->start_date);
        $newEndDate = Carbon::parse($request->end_date);
        $newStartTime = Carbon::createFromFormat('H:i:s', $request->start_time);
        $newEndTime = Carbon::createFromFormat('H:i:s', $request->end_time);
        $officer_start_time = Carbon::createFromFormat('H:i:s', $workingTime->work_start_time);
        $officer_end_time = Carbon::createFromFormat('H:i:s', $workingTime->work_end_time);


        //this is the second level of checks and it also works
        if($newStartTime<$officer_start_time || $newEndTime >$officer_end_time){
            return response()->json([
                'status' => 'error',
                'message' => "The officer works only between {$workingTime->work_start_time} and {$workingTime->work_end_time}."
            ]);
        }

        $existingActivities = Activity::where('officer_id', $request->officer_id)
            ->where(function ($query) use ($newStartDate, $newEndDate) {
                $query->where('start_date', '<=', $newStartDate)
                    ->where('end_date', '>=', $newStartDate);
            })->orWhere(function ($query) use ($newEndDate, $newStartDate) {
                $query->where('start_date', '<=', $newEndDate)
                    ->where('end_date', '>=', $newEndDate);
            })
            ->where('status', "active")
            ->get();
        foreach ($existingActivities as $activity) {
            $existStartDate = Carbon::parse($activity->start_date);
            $existEndDate = Carbon::parse($activity->end_date);
            $existStartTime = Carbon::createFromFormat('H:i:s', trim($activity->start_time));
            $existEndTime = Carbon::createFromFormat('H:i:s', trim($activity->end_time));
//first condition where start_date and end_date are the same
            if ($newEndDate == $newStartDate) {
                // condition (i) under the first condition
                if ($existEndDate == $existStartDate) {
                    if (
                        ($newStartTime >= $existStartTime && $newStartTime < $existEndTime)
                        || ($newEndTime > $existStartTime && $newEndTime <= $existEndTime)
                    ) {
                        return response()->json([
                            'status' => 'error',
                            'message' => "The officer is busy between {$activity->start_time} and {$activity->end_time} on {$activity->start_date}."
                        ]);
                    }
                }else {

                    //condition (ii) under the first condition
                    if ($newStartDate > $existStartDate && $newStartDate < $existEndDate) {
                        return response()->json([
                            'status' => 'error',
                            'message' => "The officer is busy between {$activity->start_date} and {$activity->end_date}."
                        ]);
                    }

//                condition (iii) under the first condition
                    if ($newStartDate == $existStartDate) {
                        if($newStartTime>$existStartTime || $newEndTime>$existStartTime){
                            return response()->json([
                                'status' => 'error',
                                'message' => "con i -> The officer is busy between {$activity->start_time} and {$activity->end_time} on {$activity->start_date} to {$activity->end_date}."
                            ]);
                        }
                    }else if($newEndDate == $existEndDate){
                        if($newEndTime<$existEndTime || $newStartTime<$existEndTime){
                            return response()->json([
                                'status' => 'error',
                                'message' => "con ii -> The officer is busy between {$activity->start_time} and {$activity->end_time} on {$activity->start_date} to {$activity->end_date}."
                            ]);
                        }
                    }
                }
            }
            //main villain
            //left for future

        }
            try {
                Activity::create([
                    'type' => $request->type,
                    'officer_id' => $request->officer_id,
                    'start_date' => $request->start_date,
                    'end_date' => $request->end_date,
                    'start_time' => $request->start_time,
                    'end_time' => $request->end_time,
                ]);

                return response()->json([
                    'status' => 'success',
                    'message' => 'Activity Created Successfully'
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'status' => 'error',
                    'message' => $e->getMessage()
                ]);

            }
        }
    }


