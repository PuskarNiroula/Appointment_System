<?php

namespace App\Service;

use App\Models\Appointment;
use App\Models\Visitor;
use Exception;
use Illuminate\Support\Facades\DB;

class VisitorService
{
    protected AppointmentService $appointmentService;

    public function __construct(
    ) {
        $this->appointmentService = app(AppointmentService::class);
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
        DB::beginTransaction();

        try {
            Visitor::findOrFail($id)->update(['status' => 'inactive']);
            $appointments = Appointment::where('visitor_id', $id)
                ->where('status', 'active')
                ->get();

            foreach ($appointments as $appointment) {
                $this->appointmentService->deactivateAppointment($appointment->id);
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
            foreach($appointments as $appointment){
                if($appointment->start_time<=$start_time && $appointment->end_time>$end_time){
                    return false;
                }elseif ($appointment->start_time<$end_time && $appointment->end_time>=$start_time){
                    return false;
                }
            }

            return true;
        }catch (Exception){
            return false;
        }
    }
    public function getActiveVisitors(){
        return Visitor::where('status','active')->get();
    }


    public function getAllVisitors(){
        return Visitor::all();
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
}
