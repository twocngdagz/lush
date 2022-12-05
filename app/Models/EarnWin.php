<?php

namespace App\Models;

use App\Models\EarnWin\EarnWinFreeReward;
use App\Models\EarnWin\EarnWinFreeRewardByCriteria;
use App\Models\EarnWin\EarnWinRankedReward;
use App\Models\EarnWin\EarnWinRewardLimit;
use App\Models\EarnWin\EarnWinRewardPeriod;
use Illuminate\Database\Eloquent\Model;
use App\Traits\EarnsRewards;
use App\Traits\LogsAllActivity;

class EarnWin extends Model
{
    /**
     * The EarnsRewards trait adds reward functionality for
     */
    use EarnsRewards;
    use LogsAllActivity;

    protected $fillable = [
        'promotion_id',
        'earn_win_reward_id',
        'no_pin_required',
        'starts_at',
        'ends_at',
        'free_rewards',
        'daily_free_rewards'
    ];
    protected $dates = ['starts_at', 'ends_at', 'created_at', 'updated_at'];

    /**
     * Reward limit relationship
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rewardLimits()
    {
        return $this->hasMany(EarnWinRewardLimit::class);
    }

    /**
     * Promotion relationship
     *
     **/
    public function promotion()
    {
        return $this->belongsTo(Promotion::class);
    }

    /**
     * Event relationship
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rewardPeriods()
    {
        return $this->hasMany(EarnWinRewardPeriod::class)->orderBy('starts_at');
    }

    public function earnWinFreeRewards()
    {
        return $this->hasMany(EarnWinFreeReward::class);
    }

    /**
     * Earn method type relationship
     *
     **/
    public function freeRankedRewards()
    {
        return $this->hasMany(EarnWinRankedReward::class);
    }

    /**
     * Get the number of free entries available to a specific rank
     * @param  integer $externalRankId External Rank Identifier
     * @return integer
     */
    public function freeRankedRewardsByRankId($externalRankId)
    {
        $entry = $this->freeRankedRewards()->where('ext_rank_id', $externalRankId)->first();

        return $entry->value ?? 0;
    }

    /**
     * GameFreeEntryByCriteria relationship
     *
     **/
    public function freeRewardsByCriteria()
    {
        return $this->hasMany(EarnWinFreeRewardByCriteria::class);
    }
}
