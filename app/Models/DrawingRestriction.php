<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DrawingRestriction extends Model
{
    use LogsAllActivity;
    protected $guarded = [];
    protected $with = ['player'];

    /**
     * Drawing relationship
     * @return BelongsTo
     */
    public function drawing(): BelongsTo
    {
        return $this->belongsTo(Drawing::class);
    }

    /**
     * Player relationship
     * @return BelongsTo
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }
}
