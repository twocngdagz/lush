<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusRewardPeriodEarningExclusionPeriod extends Model
{
    protected $guarded = [];
    protected $dates = ['starts_at', 'ends_at'];

    public function rewardPeriod(): BelongsTo
    {
        return $this->belongsTo(BonusRewardPeriod::class);
    }


    public function containsDates(Carbon $date1, ?Carbon $date2 = null): bool
    {
        if ($date2) {
            return ($this->starts_at->between($date1, $date2)) || ($this->ends_at->between($date1, $date2, false));
        }

        return $date1->between($this->starts_at, $this->ends_at);
    }
}
