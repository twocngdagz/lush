<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BonusRewardPeriodClaimedMultiplier extends Model
{
    protected $guarded = [];

    /**
     * Rewards relationship
     */
    public function rewardPeriod(): BelongsTo
    {
        return $this->belongsTo(BonusRewardPeriod::class, 'reward_period_id')->withTimestamps();
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_id');
    }

}
