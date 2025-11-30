<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Officer;
use App\Service\ActivityService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class ActivityController extends Controller
{
    public ActivityService $service;

    public function __construct(ActivityService $service){
        $this->service = $service;
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
        $officers = Officer::all();
        return view('Activity.create_activity', compact('officers'));
    }

    public function store(Request $request): JsonResponse
    {
        // All validation remains here (unchanged)
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

        // Prepare clean data array for service
        $data = [
            'officer_id' => $request->officer_id,
            'type' => $request->type,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'start_time' => $request->start_time,
            'end_time' => $request->end_time,
        ];

        // Pass clean data to service
        $response = $this->service->store($data);

        return response()->json($response);
    }
}


