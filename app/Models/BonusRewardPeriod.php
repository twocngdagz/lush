<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BonusRewardPeriod extends Model
{
    protected $guarded = [];

    protected $dates = [
        'starts_at',
        'ends_at',
        'earning_starts_at',
        'earning_ends_at'
    ];

    /**
     * Rewards relationship
     */
    public function rewards(): BelongsToMany
    {
        return $this->belongsToMany(Reward::class, 'bonus_reward_period_reward')->withTimestamps();
    }

    /**
     * Bonus Relationship`
     */
    public function bonus(): BelongsTo
    {
        return $this->belongsTo(Bonus::class);
    }

    /**
     * Exclusion Periods Relationship
     */
    public function earningExclusionPeriods(): HasMany
    {
        return $this->hasMany(BonusRewardPeriodEarningExclusionPeriod::class);
    }


    /**
     * Get all rewards that have redemptions
     * @param Player|null $player
     * @return Builder
     */
    public function redeemedRewards(?Player $player = null): Builder
    {
        return $this->rewards()->with('redemptions')
            ->whereHas('redemptions', function ($query) use ($player) {
                if ($player) {
                    $query->where('player_id', '=', $player->id);
                }
            });
    }

    public function claimedMultipliers()
    {
        return $this->hasMany(BonusRewardPeriodClaimedMultiplier::class, 'reward_period_id');
    }

    /**
     * A bonus reward period is considered complete when we have passed the ends_at date
     * @return bool
     */
    public function isComplete()
    {
        return $this->ends_at->isPast();
    }

    /**
     * Is the reward period currently running. We are in between the starts_at and ends_at date
     * @return bool
     */
    public function isCurrent()
    {
        return $this->starts_at->isPast() && $this->ends_at->isFuture();
    }

    /**
     * Has the reward period started running.
     * @return bool
     */
    public function hasStarted()
    {
        return $this->starts_at->isPast();
    }

    /**
     * Can we edit the rewards for this reward period
     * @return bool
     */
    public function canEditRewards()
    {
        return !$this->isComplete() && !$this->isCurrent();
    }

    /**
     * Query scope to limit results to current reward periods
     * @param $query
     */
    public function scopeCurrent($query)
    {
        $query->where('starts_at', '<=', now())
            ->where('ends_at', '>=', now());
    }
}
