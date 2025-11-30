<?php
namespace App\Service;

use App\Models\Activity;
use App\Models\Appointment;
use App\Models\Visitor;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;

class AppointmentService{

    public function activateAppointment(int $id):bool{
      if(  Appointment::find($id)->exists()){
          Appointment::find($id)->update(['status'=>'active']);
          return true;
      }
    return false;
    }

    public function deactivateAppointment(int $id):bool{
        if(  Appointment::find($id)->exists()){
           $appointment= Appointment::find($id);
               $appointment->update(['status'=>'deactivated']);
            return true;
        }
        return false;
    }

    public function cancelAppointment(int $id):bool{
        if(  Appointment::find($id)->exists()){
            DB::beginTransaction();
           $appointment= Appointment::find($id);
            $appointment->update(['status'=>'cancelled']);
            Activity::where('officer_id',$appointment->officer_id)
                ->where('start_date',$appointment->appointment_date)
                ->where('end_date',$appointment->appointment_date)
                ->where('start_time',$appointment->start_time)
                ->where('end_time',$appointment->end_time)
                ->update(['status'=>'cancelled']);
            DB::commit();
            return true;
        }
        return false;
    }
    public function store(array $data):void
    {
        Appointment::create($data);
    }

}
