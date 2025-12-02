<?php

namespace App\Service;

use App\Models\Activity;
use App\Models\Appointment;
use App\Models\Officer;
use App\Models\WorkDay;
use Carbon\Carbon;
use Exception;

class ActivityService
{
    public function store(array $data): array
    {

        if(!$this->checkWorkingDay($data['officer_id'],$data['start_date'])){
            return [
                'status'  => 'error',
                'message' => "The officer does not work on.". strtolower(Carbon::parse($data['start_date'])->format('l'))
            ];
        }
        $resp=$this->checkWorkingHour($data['officer_id'],$data['start_time'],$data['end_time']);
            if($resp['status']=='error'){
                 return $resp;
             }

        // Convert values
        $newStartDate = Carbon::parse($data['start_date']);
        $newEndDate   = Carbon::parse($data['end_date']);
        $newStartTime = Carbon::createFromFormat('H:i:s', $data['start_time']);
        $newEndTime   = Carbon::createFromFormat('H:i:s', $data['end_time']);


        // Check overlaps
        $response = $this->checkIfAvailable(
            $data['id']??null,
            $data['officer_id'],
            $newStartDate,
            $newEndDate,
            $newStartTime,
            $newEndTime
        );

        if ($response['status'] === 'error') {
            return $response;
        }

        // Create activity
        try {
            if (!empty($data['id'])) {
                // Update existing record
                Activity::findOrFail($data['id'])->update($data);
            } else {
                // Create new record
                Activity::create($data);
            }


            return [
                'status'  => 'success',
                'message' => 'Activity Created Successfully'
            ];
        } catch (\Exception $e) {
            return [
                'status'  => 'error',
                'message' => $e->getMessage()
            ];
        }
    }
    private function checkIfAvailable($activity_id,$officer_id, $newStartDate, $newEndDate, $newStartTime, $newEndTime)
    {
        $existingActivities = Activity::where('officer_id', $officer_id)
            ->where('status', 'active')
            ->where(function ($query) use ($newStartDate, $newEndDate) {

                // New range overlaps existing range
                $query->where(function ($q) use ($newStartDate, $newEndDate) {
                    $q->where('start_date', '<=', $newStartDate)
                        ->where('end_date', '>=', $newStartDate);
                })

                    ->orWhere(function ($q) use ($newStartDate, $newEndDate) {
                        $q->where('start_date', '<=', $newEndDate)
                            ->where('end_date', '>=', $newEndDate);
                    });

            })
            ->get();

        if($activity_id!==null){
            $existingActivities = Activity::where('officer_id', $officer_id)
                ->where('status', 'active')
                ->where(function ($query) use ($newStartDate, $newEndDate) {

                    // New range overlaps existing range
                    $query->where(function ($q) use ($newStartDate, $newEndDate) {
                        $q->where('start_date', '<=', $newStartDate)
                            ->where('end_date', '>=', $newStartDate);
                    })

                        ->orWhere(function ($q) use ($newStartDate, $newEndDate) {
                            $q->where('start_date', '<=', $newEndDate)
                                ->where('end_date', '>=', $newEndDate);
                        });

                })->where('id','!=',$activity_id)
                ->get();
        }

        foreach ($existingActivities as $activity) {

            $existStartDate = Carbon::parse($activity->start_date);
            $existEndDate   = Carbon::parse($activity->end_date);
            $existStartTime = Carbon::createFromFormat('H:i:s', trim($activity->start_time));
            $existEndTime   = Carbon::createFromFormat('H:i:s', trim($activity->end_time));

            //
            if ($newEndDate == $newStartDate) {

                if ($existEndDate == $existStartDate) {

                    if (
                        ($newStartTime >= $existStartTime && $newStartTime < $existEndTime) ||
                        ($newEndTime > $existStartTime && $newEndTime <= $existEndTime)
                    ) {
                        return [
                            'status'  => 'error',
                            'message' => " The officer is busy between {$activity->start_time} and {$activity->end_time} on {$activity->type}."
                        ];
                    }

                } else {

                    if ($newStartDate > $existStartDate && $newStartDate < $existEndDate) {
                        return [
                            'status'  => 'error',
                            'message' => "The officer is busy between {$activity->start_date} and {$activity->end_date}."
                        ];
                    }

                    if ($newStartDate == $existStartDate) {
                        if ($newStartTime > $existStartTime || $newEndTime > $existStartTime) {
                            return [
                                'status'  => 'error',
                                'message' => "con i -> The officer is busy between {$activity->start_time} and {$activity->end_time}."
                            ];
                        }
                    } elseif ($newEndDate == $existEndDate) {

                        if ($newEndTime < $existEndTime || $newStartTime < $existEndTime) {
                            return [
                                'status'  => 'error',
                                'message' => "con ii -> The officer is busy between {$activity->start_time} and {$activity->end_time}."
                            ];
                        }
                    }
                }
            }
        }

        // ✔ Only return success AFTER checking all activities
        return [
            'status' => 'success'
        ];
    }


    public function singleDayCheck($date,$start_time,$end_time,$activity):bool{

        //first if lies in between some date
        if($activity->start_date<$date && $activity->end_date>$date){
            return false;
        }

        //if the start-date matches with the new date
       if($activity->start_date==$date){
               if($end_time>$activity->start_time){
                   return false;
               }

       }
       //if the end-date matches with the date
       if($activity->end_date==$date){
               if($start_time<$activity->end_time){
                   return false;
               }
       }
       return true;
    }

public function multiDayCheck($start_date,$end_date,$start_time,$end_time,$activity):bool{

        //first case when the db has single-day work
    if($activity->start_date==$activity->end_date){

        //when the date is inside the db:date
        if($start_date<$activity->start_date && $end_date>$activity->end_date)
            return false;

        //when end_date matches with the db:date
        if($end_date==$activity->start_date){
            if($end_time>$activity->start_time){
                return false;
            }
        }
        //when start-date matches with the db:date
        if($start_date==$activity->start_date){
            if($start_time<$activity->end_time){
                return false;
            }
        }

    }else{
        //when activity is completely inside new activity
        if($start_date<$activity->start_date && $end_date>$activity->end_date){
            return false;
        }
        //when the new activity is completely inside the old activity
        if($activity->start_date<$start_date && $activity->end_date>$end_date){
            return false;
        }
        //when the new activity's end_date matches with the old activities start_date
        if($end_date==$activity->start_date){
            if($end_time>$activity->start_time){
                return false;
            }
        }

        //when the new activity's start_date matches with the old activity end_date
        if($start_date==$activity->end_date){
            if($start_time<$activity->end_time){
                return false;
            }
        }

    }
    return true;
}

public function checkWorkingDay($officer_id,$start_date):bool{
        $days=WorkDay::where('officer_id',$officer_id)->pluck('day_of_week')->toArray()??[];
        $requestDay=strtolower(Carbon::parse($start_date)->format('l'));
        return in_array($requestDay,$days);
}

public function checkWorkingHour($officer_id,$start_time,$end_time):array{

    $newStartTime = Carbon::createFromFormat('H:i:s', $start_time);
    $newEndTime   = Carbon::createFromFormat('H:i:s', $end_time);

    $workingTime = Officer::select('work_start_time', 'work_end_time')
        ->find($officer_id);
    $officer_start_time = Carbon::createFromFormat('H:i:s', $workingTime->work_start_time);
    $officer_end_time   = Carbon::createFromFormat('H:i:s', $workingTime->work_end_time);

    if ($newStartTime < $officer_start_time || $newEndTime > $officer_end_time) {
        return [
            'status'  => 'error',
            'message' => "The officer works only between {$workingTime->work_start_time} and {$workingTime->work_end_time}."
        ];
    }

return [
    'status'=>'success'
];
}

public function cancel(Activity $activity):array{
            $activity->update(['status'=>'cancelled']);
            return [
                'status'=>'success',
                'message'=>'Activity cancelled successfully'
            ];
}




}
