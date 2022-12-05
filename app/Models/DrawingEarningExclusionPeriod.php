<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrawingEarningExclusionPeriod extends Model
{
    protected $guarded = [];
    protected $dates = ['starts_at', 'ends_at'];

    /**
     * @return BelongsTo
     */
    public function drawing(): BelongsTo
    {
        return $this->belongsTo(Drawing::class);
    }

    /**
     * Does this period include the dates provided
     * @param $date1
     * @param null $date2
     * @return bool
     */
    public function containsDates(Carbon $date1, ?Carbon $date2 = null): bool
    {
        if ($date2) {
            return ($this->starts_at->between($date1, $date2)) || ($this->ends_at->between($date1, $date2, false));
        }

            return $date1->between($this->starts_at, $this->ends_at);
    }
}
