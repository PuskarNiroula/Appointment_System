<?php

namespace Database\Seeders;

use Faker\Factory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class VisitorsSeeder extends Seeder
{
    public function run()
    {
        $faker = Factory::create();

        $data = [];

        for ($i = 1; $i <= 22; $i++) {
            $data[] = [
                'name'        => $faker->name,
                'mobile_num'  => $faker->numerify('98########'),
                'email'       => $faker->unique()->safeEmail,
                'status'      => $faker->randomElement(['active', 'inactive']),
                'created_at'  => now(),
                'updated_at'  => now(),
            ];
        }

        DB::table('visitors')->insert($data);
    }
}
