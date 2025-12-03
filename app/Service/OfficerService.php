<?php

namespace App\Service;

use App\Models\Activity;
use App\Models\Appointment;
use App\Models\Officer;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class OfficerService
{
    protected AppointmentService $appointmentService;

    public function __construct()
    {
        $this->appointmentService = app(AppointmentService::class);
    }

    public function checkIfActive(int $id): bool
    {
        $officer = Officer::find($id);

        return $officer && $officer->status === 'active';
    }

    public function activateOfficer(int $id): bool
    {
        DB::beginTransaction();

        try {
            // Find the officer
            $officer = Officer::findOrFail($id);

            if($officer->post->status=='inactive'){
                return false;
            }

            $now = Carbon::now('Asia/Kathmandu');
            $appointments = Appointment::where('officer_id', $id)
                ->where(function ($query) use ($now) {
                    $query->where('appointment_date', '>', $now->toDateString())
                        ->orWhere(function ($q) use ($now) {
                            $q->where('appointment_date', $now->toDateString())
                                ->where('start_time', '>=', $now->toTimeString());
                        });
                })
                ->with('visitor')
                ->get();

            foreach ($appointments as $appointment) {
                if ($appointment->visitor && $appointment->visitor->status === 'active') {
                    $this->appointmentService->activateAppointment($appointment->id);
                    Activity::where('officer_id', $appointment->officer_id)
                        ->where('start_date', $appointment->appointment_date)
                        ->where('end_date', $appointment->appointment_date)
                        ->where('start_time', $appointment->start_time)
                        ->where('end_time', $appointment->end_time)
                        ->update(['status' => 'active']);
                }
            }

            // Activate officer
            $officer->update(['status' => 'active']);

            DB::commit();
            return true;

        } catch (Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function deactivateOfficer(int $id): bool
    {
        DB::beginTransaction();
        try {
            $now = Carbon::now('Asia/Kathmandu');
            $officer = Officer::findOrFail($id);
            $appointments = Appointment::where('officer_id', $id)
                ->where(function ($query) use ($now) {
                    $query->where('appointment_date', '>', $now->toDateString())
                        ->orWhere(function ($q) use ($now) {
                            $q->where('appointment_date', $now->toDateString())
                                ->where('start_time', '>=', $now->toTimeString());
                        });
                })
                ->get();
            foreach ($appointments as $appointment) {
                $this->appointmentService->deactivateAppointment($appointment->id);
            }
            $activities=Activity::where('officer_id',$id)
                 ->where(function ($query) use ($now) {
                     $query->where('end_date', '>', $now->toDateString())
                         ->orWhere(function ($q) use ($now) {
                             $q->where('end_date', $now->toDateString())
                                 ->where('start_time', '>=', $now->toTimeString());
                         });
                 })
                 ->get();
            foreach ($activities as $activity){
                $activity->update(['status'=>'deactivated']);
            }
            $officer->update(['status' => 'inactive']);
            DB::commit();
            return true;
        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }

    public function getWorkingOfficers()
    {
        return Officer::whereHas('workDay')->where('status','active')->get();
    }
    public function getAllOfficers(){
        return Officer::all();
    }
    public function findOrFail(int $id){
        return Officer::findOrFail($id);
    }
    public function getQuery(){
//        return Officer::with('workDay')->withCount('workDay')->orderByDesc('work_day_count');

        return Officer::join('posts', 'posts.id', '=', 'officers.post_id')
            ->select('officers.*', 'posts.name as post_name') // alias for search
            // still eager-load relationships if needed
            ->withCount('workDay');

    }
    public function createOfficer($data):array{
        try {
            Officer::create([
                'name' => $data->name,
                'post_id' => $data->post_id,
                "work_start_time" => $data->start_time,
                "work_end_time" => $data->end_time,
            ]);
          return ['status'=>'success'];
        }catch (Exception $e){
         return ['status'=>'error',
             'message'=>$e->getMessage()
         ];
        }
    }
    public function updateOfficer(int $id, array $data):array{
       try{
           $this->findOrFail($id)->update([
               'name' => $data['name'],
               'post_id' => $data['post_id'],
               "work_start_time" => $data['start_time'],
               "work_end_time" => $data['end_time']
           ]);
         return ['status'=>'success'];
       }
       catch (Exception $e){
           return ['status'=>'error',
               'message'=>$e->getMessage()
           ];
       }
    }

    public function getOfficerByPostId(int $postId):bool{
       return Officer::where('post_id',$postId)->exists();
    }

    public function getActiveOfficerCount(){
        return Officer::where('status','active')->count();
    }
}
