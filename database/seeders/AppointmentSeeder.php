<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\Appointment;
use Illuminate\Database\Seeder;

class AppointmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $officer_ids=[1,2,3,7,8,9];
        $visitor_ids=[1,2,3,4,5,6];


        $date='2025-12-02';

        $i=0;
        foreach ($officer_ids as $id) {

            Appointment::create([
                'visitor_id'=>$visitor_ids[$i++],
                'officer_id'=>$id,
                'appointment_date'=>$date,
                'start_time'=>'13:00:00',
                'end_time'=>'15:00:00',
            ]);
            Activity::create([
                'type'=>'appointment',
                'officer_id'=>$id,
                'start_date'=>$date,
                'end_date'=>$date,
                'start_time'=>'13:00:00',
                'end_time'=>'15:00:00',
            ]);
        }

    }
}
