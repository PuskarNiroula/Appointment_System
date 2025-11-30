<?php

namespace App\Service;

use App\Models\Appointment;
use App\Models\Visitor;
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

        } catch (\Exception $e) {
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
            // Deactivate visitor
            Visitor::findOrFail($id)->update(['status' => 'inactive']);

            // Fetch active appointments
            $appointments = Appointment::where('visitor_id', $id)
                ->where('status', 'active')
                ->get();

            foreach ($appointments as $appointment) {
                $this->appointmentService->deactivateAppointment($appointment->id);
            }

            DB::commit();
            return true;

        } catch (\Exception $e) {
            DB::rollBack();
            return false;
        }
    }
    public function checkIfActive(int $id):bool{
       if( Visitor::where('id',$id)->where('status','active')->exists())
           return true;
       return false;
    }
}
