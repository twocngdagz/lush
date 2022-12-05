<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Traits\LogsAllActivity;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PromotionRestriction extends Model
{
    use LogsAllActivity;

    protected $guarded = [];

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }
}
