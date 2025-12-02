<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Appointment;
use App\Models\Officer;
use App\Models\Post;
use App\Models\WorkDay;
use App\Service\OfficerService;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Yajra\DataTables\DataTables;

class OfficerController extends Controller
{
    protected OfficerService $service;

    public function __construct(OfficerService $service)
    {
        $this->service = $service;
    }

    public function index(): view
    {
        return view('Officer.index');
    }

    public function create(): View
    {
        $posts = Post::select('id', 'name')->where('status', 'active')->orderBy('name')->get();
        return view('Officer.create_officer', compact('posts'));
    }

    public function edit(int $id): View
    {
        $officer = Officer::findOrFail($id);
        $posts = Post::select('id', 'name')
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('Officer.update_officer', compact('officer', 'posts'));
    }

    public function activate(int $id): JsonResponse
    {

        if ($this->service->activateOfficer($id)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Officer Activated Successfully'
            ]);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'Officer Activation Failed'
        ]);
    }

    public function deactivate(int $id): JsonResponse
    {
        if ($this->service->deactivateOfficer($id)) {
            return response()->json([
                'status' => 'success',
                'message' => 'Officer Deactivated Successfully'
            ]);
        }
        return response()->json([
            'status' => 'error',
            'message' => 'Officer Deactivation Failed'
        ]);
    }


    /**
     * @throws Exception
     */
    public function getOfficers(): JsonResponse
    {
        $query = Officer::get();

        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('work_day', function ($officer) {
                $work_days=[];
                foreach ($officer->workDay as $work_day){
                    $work_days[]=$work_day->day_of_week;
                }
                return implode(', ',$work_days);
            })
            // Searchable column: post.name
          ->addColumn('post',function ($officer){
              return $officer->post->name;
            })
            ->make();
    }



    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'post_id' => 'required|exists:posts,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time'
        ]);
        try {
            Officer::create([
                'name' => $request->name,
                'post_id' => $request->post_id,
                "work_start_time" => $request->start_time,
                "work_end_time" => $request->end_time,
            ]);
            return response()->json([
                'status' => 'success',
                'message' => 'Officer Created Successfully'
            ]);
        } catch (Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $officer = Officer::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'post_id' => 'required|exists:posts,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time'
        ]);

        try {

            DB::beginTransaction();

            // 1. Update officer
            $officer->update([
                'name' => $request->name,
                'post_id' => $request->post_id,
                "work_start_time" => $request->start_time,
                "work_end_time" => $request->end_time,
            ]);

            // Local time
            $today = Carbon::now('Asia/Kathmandu')->toDateString();

            // 2. Cancel activities outside new working hours
            Activity::where('officer_id', $id)
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->where('start_date', '>=', $today)
                ->where(function ($q) use ($request) {
                    $q->where('start_time', '<', $request->start_time)
                        ->orWhere('end_time', '>', $request->end_time);
                })
                ->update(['status' => 'cancelled']);

            // 3. Cancel appointments outside new working hours
            Appointment::where('officer_id', $id)
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->where('appointment_date', '>=', $today)
                ->where(function ($q) use ($request) {
                    $q->where('start_time', '<', $request->start_time)
                        ->orWhere('end_time', '>', $request->end_time);
                })
                ->update(['status' => 'cancelled']);;
            DB::commit();
            return response()->json([
                'status' => 'success',
                'message' => 'Officer Updated Successfully'
            ]);

        } catch (Exception $e) {

            DB::rollBack();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }


    public function assignDays(int $id): View
    {
        $officer = Officer::findOrFail($id);

        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $existingDays = WorkDay::where('officer_id', $id)->pluck('day_of_week')->toArray() ?? [];

        return view('Officer.assign_working_days', compact('officer', 'days', 'existingDays'));
    }

    public function saveWorkingDays(int $id, Request $request): RedirectResponse
    {
        $request->validate([
            'days' => 'required|array',
            'days.*' => 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
        ]);

        DB::beginTransaction();

        try {

            // Reset officer days
            WorkDay::where('officer_id', $id)->delete();

            // Insert new working days
            $insert = [];
            foreach ($request->days as $day) {
                $insert[] = [
                    'officer_id' => $id,
                    'day_of_week' => strtolower($day)
                ];
            }

            WorkDay::insert($insert);

            // Cancel activities that fall on non-working days
            $today = Carbon::now('Asia/Kathmandu')->toDateString();

            $activities = Activity::where('officer_id', $id)
                ->where('end_date', '>=', $today)
                ->whereNotIn('status', ['cancelled', 'completed'])
                ->get();

            foreach ($activities as $activity) {

                // Single-day activity
                if ($activity->start_date == $activity->end_date) {

                    $dayOfActivity = strtolower(Carbon::parse($activity->start_date)->format('l'));

                    // If this day is NOT in working days → cancel
                    if (!in_array($dayOfActivity, $request->days)) {
                        // Cancel matching appointment
                        if ($activity->type === 'appointment') {
                            Appointment::where('officer_id', $id)
                                ->where('appointment_date', $activity->start_date)
                                ->whereNotIn('status', ['cancelled', 'completed'])
                                ->update(['status' => 'cancelled']);
                        }
                        // Cancel activity
                        $activity->update(['status' => 'cancelled']);
                    }
                }
            }

            DB::commit();

            return redirect()->route('officer.index')
                ->with('success', 'Working days updated successfully.');

        } catch (Exception $e) {

            DB::rollBack();

            return redirect()->back()->withErrors($e->getMessage());
        }
    }

}
