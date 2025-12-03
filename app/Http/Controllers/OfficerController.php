<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\Appointment;
use App\Service\AppointmentService;
use App\Service\OfficerService;
use App\Service\PostService;
use App\Service\WorkDayService;
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
    protected AppointmentService $appointmentService;
    protected PostService $postService;
    protected WorkDayService $workDayService;

    public function __construct()
    {
        $this->service = app(OfficerService::class);
        $this->appointmentService = app(AppointmentService::class);
        $this->postService = app(PostService::class);
        $this->workDayService = app(WorkDayService::class);
    }

    public function index(): view
    {
        return view('Officer.index');
    }

    public function create(): View
    {
        $posts=$this->postService->getActivePostNameAndId();
        return view('Officer.create_officer', compact('posts'));
    }

    public function edit(int $id): View
    {
        $officer =$this->service->findOrFail($id);
        $posts = $this->postService->getActivePostNameAndId();

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
            'message' => 'Activate Related Post First'
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
    public function getOfficers(Request $request): JsonResponse
    {
        $query = $this->service->getQuery();
        return DataTables::of($query)
            ->filter(function ($query) use ($request) {
                if ($request->has('search') && $request->search['value']) {
                    $search = $request->search['value'];
                    $query->where(function($q) use ($search) {
                        $q->where('officers.name', 'like', "%{$search}%")
                            ->orWhere('posts.name', 'like', "%{$search}%"); // search by post name
                    });
                }
            })
            ->addColumn('post', function($row) {
                return $row->post_name; // use the alias from select
            })
            ->addColumn('work_day', function($query) {
                $days=[];
                foreach($query->workDay as $day){
                    $days[]=$day->day_of_week;
                }
                return implode(', ',$days);
            })
            ->orderColumn('work_day_count', function ($query, $order) {
                $query->orderBy('work_day_count', $order);
            })
            ->make(true);
    }

    /**
     * @throws Exception
     */
    public function viewAppointments(int $id): JsonResponse
    {
        $query =$this->appointmentService->getAppointmentsQuery($id);
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('visitor_name', function ($appointment) {
                return $appointment->visitor->name;
            })
            ->make(true);
    }

    public function appointments(int $id): View
    {
        $officer=$this->service->findOrFail($id);
        return view('Officer.view_appointment',compact('officer'));
    }



    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'post_id' => 'required|exists:posts,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time'
        ]);

           $response= $this->service->createOfficer($request->all());
           if($response['status']=='error')
               return response()->json($response);
            return response()->json([
                'status' => 'success',
                'message' => 'Officer Created Successfully'
            ]);
    }

    public function update(int $id, Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'post_id' => 'required|exists:posts,id',
            'start_time' => 'required|date_format:H:i',
            'end_time' => 'required|date_format:H:i|after:start_time'
        ]);

        try {

            DB::beginTransaction();

            // 1. Update officer
           $response = $this->service->updateOfficer($id, $request->all());
           if($response['status']=='error'){
               return response()->json($response);
           }

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
        $officer = $this->service->findOrFail($id);

        $days = ['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'];
        $existingDays = $this->workDayService->getExistingDays($id);

        return view('Officer.assign_working_days', compact('officer', 'days', 'existingDays'));
    }

    public function saveWorkingDays(int $id, Request $request): RedirectResponse
    {
        $request->validate([
            'days' => 'required|array',
            'days.*' => 'in:sunday,monday,tuesday,wednesday,thursday,friday,saturday',
        ]);
        $this->service->findOrFail($id);
        DB::beginTransaction();

        try {

            // Reset officer days
           $this->workDayService->deleteWorkDayOfOfficer($id);


            foreach ($request->days as $day) {
                $data=[
                    'officer_id' => $id,
                    'day_of_week' => strtolower($day)
                ];
                $this->workDayService->createWorkDay($data);
            }

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
