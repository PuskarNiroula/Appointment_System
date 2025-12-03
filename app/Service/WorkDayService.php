<?php
namespace App\Service;

use App\Models\WorkDay;

class WorkDayService{
    public function getExistingDays(int $officerId):?array{
        return WorkDay::where('officer_id',$officerId)->pluck('day_of_week')->toArray();
    }
    public function deleteWorkDayOfOfficer(int $officerId):bool{
        return WorkDay::where('officer_id',$officerId)->delete();
    }
    public function createWorkDay(array $data){
        return WorkDay::create($data);
    }
}
