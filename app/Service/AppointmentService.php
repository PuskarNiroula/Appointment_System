<?php
namespace App\Service;

use App\Models\Activity;
use App\Models\Appointment;
use Exception;
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
        try{

            DB::beginTransaction();
           $appointment= $this->getById($id);;
            $appointment->update(['status'=>'cancelled']);
            Activity::where('officer_id',$appointment->officer_id)
                ->where('start_date',$appointment->appointment_date)
                ->where('end_date',$appointment->appointment_date)
                ->where('start_time',$appointment->start_time)
                ->where('end_time',$appointment->end_time)
                ->update(['status'=>'cancelled']);
            DB::commit();
            return true;
        }catch (Exception $e){
            DB::rollBack();
            return false;
        }
    }
    public function store(array $data): void
    {
        $newDate = $data['appointment_date'];
        $newStart = $data['start_time'];
        $newEnd = $data['end_time'];
        $officer = $data['officer_id'];

        // Check if a deactivated appointment overlaps the new one
        $conflicting = Appointment::where('officer_id', $officer)
            ->where('appointment_date', $newDate)
            ->where('status', 'deactivated')
            ->where(function ($q) use ($newStart, $newEnd) {
                $q->where('start_time', '<', $newEnd)
                    ->where('end_time', '>', $newStart);
            })
            ->get();

        if ($conflicting->isNotEmpty()) {

            DB::beginTransaction();

            // Cancel all conflicting deactivated appointments
            Appointment::whereIn('id', $conflicting->pluck('id'))
                ->update(['status' => 'cancelled']);

            // Also cancel related activity
            Activity::where('officer_id', $officer)
                ->where('start_date', $newDate)
                ->where('end_date', $newDate)
                ->where(function ($q) use ($newStart, $newEnd) {
                    $q->where('start_time', '<', $newEnd)
                        ->where('end_time', '>', $newStart);
                })
                ->where('type', 'appointment')
                ->where('status', 'active')
                ->update(['status' => 'cancelled']);

            DB::commit();
        }

        Appointment::create($data);
    }


    public function getAppointmentsQuery(int $id){
        return Appointment::where('officer_id',$id)
            ->orderBy('status')
            ->orderBy('appointment_date','desc')
            ->orderBy('start_time');
    }
    public function getQuery(){
        return Appointment::orderBy('status')->orderBy('appointment_date','desc')->orderBy('start_time');
    }
    public function cancel(int $id):bool{

        try{
            $appointment= $this->getById($id);;
            $appointment->update(['status'=>'cancelled']);
           return true;
        }catch (Exception $e){
           return false;
        }
    }



    /**
     * @throws Exception
     */
    public function getById(int $id){
        $appointment=Appointment::find($id);
        if(!$appointment)
            throw new Exception("Appointment Not Found");
        return $appointment;
    }




}
