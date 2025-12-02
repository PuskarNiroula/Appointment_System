<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Models\Activity;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class AutoCompleteRecords extends Command
{
    protected $signature = 'auto:complete-records';
    protected $description = 'Automatically mark appointments and activities as completed when their end time passes';

    public function handle()
    {
        $now = Carbon::now('Asia/Kathmandu');

        DB::beginTransaction();
        // Complete appointments
       $appointments= Appointment::where('status', 'active')
            ->where(function ($q) use ($now) {
                $q->whereDate('appointment_date', '<', $now->toDateString())
                    ->orWhere(function ($q2) use ($now) {
                        $q2->whereDate('appointment_date', $now->toDateString())
                            ->whereTime('end_time', '<=', $now->toTimeString());
                    });
            })
            ->get();
       foreach($appointments as $appointment){
           $appointment->update(['status' => 'completed']);
           Activity::where('officer_id', $appointment->officer_id)
               ->where('start_date',$appointment->appointment_date)
               ->where('end_date',$appointment->appointment_date)
               ->where('start_time',$appointment->start_time)
               ->where('end_time',$appointment->end_time)
               ->update(['status' => 'completed']);
       }

        // Cancel appointments
      $deactivated_appointments=  Appointment::where('status', 'deactivated')
            ->where(function ($q) use ($now) {
                $q->whereDate('appointment_date', '<', $now->toDateString())
                    ->orWhere(function ($q2) use ($now) {
                        $q2->whereDate('appointment_date', $now->toDateString())
                            ->whereTime('end_time', '<=', $now->toTimeString());
                    });
            })
            ->get();

       foreach($deactivated_appointments as $appointment){
           $appointment->update(['status' => 'cancelled']);
           Activity::where('officer_id', $appointment->officer_id)
               ->where('start_date',$appointment->appointment_date)
               ->where('end_date',$appointment->appointment_date)
               ->where('start_time',$appointment->start_time)
               ->where('end_time',$appointment->end_time)
               ->update(['status' => 'cancelled']);
       }
        DB::commit();

        $this->info("Auto-complete job executed successfully.");

        return Command::SUCCESS;
    }

}
