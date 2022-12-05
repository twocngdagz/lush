<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrawingEarningExclusionSchedule extends Model
{
    protected $guarded = [];

    public function drawing(): BelongsTo
    {
        return $this->belongsTo(Drawing::class);
    }

    /**
     * Does this schedule include the dates provided
     * @param $date1
     * @param null $date2
     * @return bool
     */
    public function containsDates(Carbon $date1, ?Carbon$date2 = null): bool
    {
        if ($date2) {
            return false;
        }

        // Is is the same day of the week
        if ($this->day_of_week !== $date1->dayOfWeek - 1) {
            return false;
        }

        // Is it an all day exclusion
        if ($this->all_day) {
            return true;
        }

        // Is it between the start and end time
        return $date1->toTimeString() >= $this->start_time && $date1->toTimeString() <= $this->end_time;
    }
}
