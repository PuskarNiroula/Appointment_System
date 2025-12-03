<?php

namespace App\Service;

use App\Models\Activity;
use App\Models\Appointment;
use App\Models\Visitor;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class VisitorService
{
    protected AppointmentService $appointmentService;

    public function __construct(
    ) {
        $this->appointmentService = app(AppointmentService::class);
    }

    /**
     * @return Builder
     */
    public function getQuery(){
        return Visitor::query();
    }

    /**
     * Activate a visitor and their appointments if the officer is active
     */
    public function activateVisitor(int $id): bool
    {
        DB::beginTransaction();

        try {
            // Activate visitor
            Visitor::findOrFail($id)->update(['status' => 'active']);

            // Fetch deactivated appointments
            $appointments = Appointment::where('visitor_id', $id)
                ->where('status', 'deactivated')
                ->with('officer')
                ->get();

            foreach ($appointments as $appointment) {
                if ($appointment->officer->status === 'active') {
                    $this->appointmentService->activateAppointment($appointment->id);
                    Activity::where('officer_id', $appointment->officer_id)
                        ->where('start_date', $appointment->appointment_date)
                        ->where('end_date', $appointment->appointment_date)
                        ->where('start_time', $appointment->start_time)
                        ->where('end_time', $appointment->end_time)
                        ->update(['status' => 'active']);
                }
            }
            DB::commit();
            return true;

        } catch (Exception) {
            DB::rollBack();
            return false;
        }
    }

    /**
     * Deactivate a visitor and all their active appointments
     */
    public function deactivateVisitor(int $id): bool
    {
        $now=Carbon::now('Asia/Kathmandu');
        DB::beginTransaction();

        try {
            Visitor::findOrFail($id)->update(['status' => 'inactive']);
            $appointments = Appointment::where('visitor_id', $id)
                ->where(function ($query) use ($now) {
                    $query->where('appointment_date', '>', $now->toDateString())
                        ->orWhere(function ($q) use ($now) {
                            $q->where('appointment_date', $now->toDateString())
                                ->where('start_time', '>=', $now->toTimeString());
                        });
                })->where('status','active')
                ->get();

            foreach ($appointments as $appointment) {
                $this->appointmentService->deactivateAppointment($appointment->id);
                Activity::where('officer_id', $appointment->officer_id)
                    ->where('start_date', $appointment->appointment_date)
                    ->where('end_date', $appointment->appointment_date)
                    ->where('start_time', $appointment->start_time)
                    ->where('end_time', $appointment->end_time)
                    ->update(['status' => 'deactivated']);
            }

            DB::commit();
            return true;

        } catch (Exception) {
            DB::rollBack();
            return false;
        }
    }
    public function checkIfActive(int $id):bool{
       if( Visitor::where('id',$id)->where('status','active')->exists())
           return true;
       return false;
    }
    public function checkIfAvailable(int $id,$date,$start_time,$end_time):bool{
        try{
            Visitor::findOrFail($id);
            $appointments=Appointment::where('visitor_id',$id)
                ->where('appointment_date',$date)
                ->whereNotIn('status',['cancelled','completed'])
                ->get();
        return $this->checkAvailability($appointments,$start_time,$end_time);
        }catch (Exception){
            return false;
        }
    }

    public function checkIfAvailableForUpdate(int $id,$date,$start_time,$end_time,$appointment_id):bool{
        try{
            Visitor::findOrFail($id);
            $appointments=Appointment::where('visitor_id',$id)
                ->where('appointment_date',$date)
                ->whereNotIn('status',['cancelled','completed'])
                ->where('id','!=',$appointment_id)
                ->get();
            return $this->checkAvailability($appointments,$start_time,$end_time);
        }catch (Exception){
            return false;
        }
    }

    private function checkAvailability($appointments,$start_time,$end_time):bool{
        foreach($appointments as $appointment){
            if($appointment->start_time<=$start_time && $appointment->end_time>$end_time){
                if($appointment->status=='deactivated'){
                    $appointment->update(['status'=>'cancelled']);
                    continue;
                }
                return false;
            }elseif ($appointment->start_time<$end_time && $appointment->end_time>=$start_time){
                if($appointment->status=='deactivated'){
                    $appointment->update(['status'=>'cancelled']);
                    continue;
                }
                return false;
            }
        }
        return true;
    }

    public function getActiveVisitors(){
        return Visitor::where('status','active')->get();
    }

    /**
     * @throws Exception
     */
    public function getById(int $id){
        $visitor=Visitor::find($id);
        if(!$visitor)
            throw new Exception("Visitor Not Found");
        return $visitor;
    }
    public function getInactiveVisitorCount(){
        return Visitor::where('status','inactive')->count();
    }
}
