<?php

namespace App\Service;

use App\Models\Appointment;
use App\Models\Officer;
use App\Models\WorkDay;
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
            // Get only future appointments
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
            $now = Carbon::now();
            $officer = Officer::findOrFail($id);
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
                $this->appointmentService->deactivateAppointment($appointment->id);
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
        return Officer::whereHas('workDay')->get();
    }
    public function getAllOfficers(){
        return Officer::all();
    }

}
