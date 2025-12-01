<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Models\Activity;
use Carbon\Carbon;

class AutoCompleteRecords extends Command
{
    protected $signature = 'auto:complete-records';
    protected $description = 'Automatically mark appointments and activities as completed when their end time passes';

    public function handle()
    {
        $now = Carbon::now('Asia/Kathmandu');

        // Complete appointments
        Appointment::where('status', 'active')
            ->where('appointment_date', '<', $now->toDateString())
            ->orWhere(function ($query) use ($now) {
                $query->where('appointment_date', '=', $now->toDateString())
                    ->where('end_time', '<=', $now->toTimeString());
            })
            ->update(['status' => 'completed']);

        //cancel appointments
        Appointment::where('status', 'deactivated')
            ->where('appointment_date', '<', $now->toDateString())
            ->orWhere(function ($query) use ($now) {
                $query->where('appointment_date', '=', $now->toDateString())
                    ->where('end_time', '<=', $now->toTimeString());
            })
            ->update(['status' => 'cancelled']);

        // Complete activities
        Activity::where('status', 'active')
            ->where('end_date', '<', $now->toDateString())
            ->orWhere(function ($query) use ($now) {
                $query->where('end_date', '=', $now->toDateString())
                    ->where('end_time', '<=', $now->toTimeString());
            })
            ->update(['status' => 'completed']);



        $this->info("Auto-complete job executed successfully.");

        return Command::SUCCESS;
    }
}
