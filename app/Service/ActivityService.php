<?php

namespace App\Service;

use App\Models\Activity;
use App\Models\Officer;
use App\Models\WorkDay;
use Carbon\Carbon;
use Exception;

class ActivityService
{
    public function store(array $data): array
    {
        // Convert values
        $newStartDate = Carbon::parse($data['start_date']);
        $newEndDate   = Carbon::parse($data['end_date']);
        $newStartTime = Carbon::createFromFormat('H:i:s', $data['start_time']);
        $newEndTime   = Carbon::createFromFormat('H:i:s', $data['end_time']);

        // Check working days
        $workingDays = WorkDay::where('officer_id', $data['officer_id'])
            ->pluck('day_of_week')
            ->toArray();

        $requestDay = strtolower($newStartDate->format('l'));

        if (!in_array($requestDay, $workingDays)) {
            return [
                'status'  => 'error',
                'message' => "The officer does not work on {$requestDay}."
            ];
        }

        // Officer working hours
        $workingTime = Officer::select('work_start_time', 'work_end_time')
            ->find($data['officer_id']);

        $officer_start_time = Carbon::createFromFormat('H:i:s', $workingTime->work_start_time);
        $officer_end_time   = Carbon::createFromFormat('H:i:s', $workingTime->work_end_time);

        // Time range check
        if ($newStartTime < $officer_start_time || $newEndTime > $officer_end_time) {
            return [
                'status'  => 'error',
                'message' => "The officer works only between {$workingTime->work_start_time} and {$workingTime->work_end_time}."
            ];
        }

        // Check overlaps
        $response = $this->checkIfAvailable(
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
            Activity::create($data);

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
    private function checkIfAvailable($officer_id, $newStartDate, $newEndDate, $newStartTime, $newEndTime)
    {
        $existingActivities = Activity::where('officer_id', $officer_id)
            ->whereNotIn('status', ['cancelled','completed'])
            ->where(function ($query) use ($newStartDate, $newEndDate) {
                $query->where('start_date', '<=', $newStartDate)
                    ->where('end_date', '>=', $newStartDate);
            })
            ->orWhere(function ($query) use ($newEndDate, $newStartDate) {
                $query->where('start_date', '<=', $newEndDate)
                    ->where('end_date', '>=', $newEndDate);
            })
            ->where('status', 'active')
            ->get();

        foreach ($existingActivities as $activity) {

            $existStartDate = Carbon::parse($activity->start_date);
            $existEndDate   = Carbon::parse($activity->end_date);
            $existStartTime = Carbon::createFromFormat('H:i:s', trim($activity->start_time));
            $existEndTime   = Carbon::createFromFormat('H:i:s', trim($activity->end_time));

            // your original logic (unchanged)
            if ($newEndDate == $newStartDate) {

                if ($existEndDate == $existStartDate) {

                    if (
                        ($newStartTime >= $existStartTime && $newStartTime < $existEndTime) ||
                        ($newEndTime > $existStartTime && $newEndTime <= $existEndTime)
                    ) {
                        return [
                            'status'  => 'error',
                            'message' => "The officer is busy between {$activity->start_time} and {$activity->end_time} on {$activity->start_date}."
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

}
