<?php
namespace App\Service;

use App\Models\Appointment;
use App\Models\Visitor;
use Illuminate\Support\Facades\App;

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
            Appointment::find($id)->update(['status'=>'deactivated']);
            return true;
        }
        return false;
    }

    public function cancelAppointment(int $id):bool{
        if(  Appointment::find($id)->exists()){
            Appointment::find($id)->update(['status'=>'cancelled']);
            return true;
        }
        return false;
    }
    public function store(array $data):void
    {
        Appointment::create($data);
    }
}
