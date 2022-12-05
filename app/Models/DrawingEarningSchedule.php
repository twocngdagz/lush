<?php

namespace App\Models;

use App\Traits\HasDaysOfWeek;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrawingEarningSchedule extends Model
{
    use HasDaysOfWeek;

    protected $guarded = [];

    /**
     * Drawing relationship
     * @return BelongsTo
     */
    public function drawing(): BelongsTo
    {
        return $this->belongsTo(Drawing::class);
    }
}
