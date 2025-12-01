<?php

namespace Database\Seeders;

use App\Models\WorkDay;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkingDaysSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $days = ['sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];
        $officer_ids=[1,2,3,7,8,9];

        foreach($officer_ids as $id){
            foreach($days as $day){
                WorkDay::create([
                    'day_of_week'=>strtolower($day),
                    'officer_id'=>$id
                ]);
            }
        }
    }
}
