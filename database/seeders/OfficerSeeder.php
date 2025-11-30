<?php

namespace Database\Seeders;

use App\Models\Officer;
use Illuminate\Database\Seeder;
use Carbon\Carbon;

class OfficerSeeder extends Seeder
{
    public function run(): void
    {
        $officers = [
            'Puskar Niroula',
            "Samana Dahal",
            'Ramesh Shrestha',
            'Sita Rana',
            'Binod Gautam',
            'Kamal Thapa',
            'Anita Karki',
            'Sunil Magar',
            'Prakash Shahi',
            'Nirjala Pandey',
            'Roshan Acharya',
            'Mina Khadka',
            'Kiran Basnet',
            'Suresh Koirala',
            'Rekha Kunwar',
            'Arjun Bista',
            'Dinesh Bohora',
            'Kalpana Gurung',
            'Suman Kharel',
            'Usha Adhikari',
            'Ganesh KC',
            'Hemanta Chaudhary',
        ];

        foreach ($officers as $name) {
            Officer::create([
                'name' => $name,
                'post_id' => rand(1, 50),
                'status' => 'active',
                'work_start_time' => '09:00:00',
                'work_end_time' => '17:00:00',
            ]);
        }
    }
}
