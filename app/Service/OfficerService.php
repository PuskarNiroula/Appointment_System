<?php
namespace App\Service;

use App\Models\Appointment;
use App\Models\Officer;
use Illuminate\Support\Facades\DB;

class OfficerService{
    protected AppointmentService $appointmentService;

    public function __construct(){
        $this->appointmentService=app(AppointmentService::class);
    }

    public function checkIfActive(int $id): bool
    {
        $officer = Officer::find($id);

        return $officer && $officer->status === 'active';
    }

    public function activateOfficer(int $id):bool{
        DB::beginTransaction();
        try{
            $officer=Officer::findOrFail($id);

            $appointments=Appointment::where('officer_id',$id)->with('visitor')->get();
            foreach ($appointments as $appointment){
                if($appointment->visitor->status==='active'){
                    $this->appointmentService->activateAppointment($appointment->id);
                }
            }
            $officer->update(['status'=>'active']);
            DB::commit();
            return true;
        }catch (\Exception $e){
            DB::rollBack();
            return false;
        }
    }
    public function deactivateOfficer(int $id):bool{
      DB::beginTransaction();
      try{
         $officer= Officer::findOrFail($id);
          $appointments=Appointment::where('officer_id',$id)->where('status','active')->get();
          foreach ($appointments as $appointment){
              $this->appointmentService->deactivateAppointment($appointment->id);
          }
    $officer->update(['status'=>'inactive']);
          DB::commit();
          return true;
      }catch (\Exception $e){
          DB::rollBack();
          return false;
      }
    }

}
