<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Origin;

class DrawingRewardLimit extends Model
{
    use LogsAllActivity;
    protected $guarded = [];

    /**
     * Drawing relationship
     * @return BelongsTo [type] [description]
     */
    public function drawing(): BelongsTo
    {
        return $this->belongsTo(Drawing::class);
    }

    /**
     * Getter for rank name
     * @param string $default
     * @return string
     */
    public function getRankName(string $default = '-'): string
    {
        return Origin::getPropertyRanks()->keyBy('id')->get($this->rank_id)->name ?? $default;
    }
}
