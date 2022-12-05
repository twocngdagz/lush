<?php

namespace App\Traits;


use App\Models\EarningMethodType;
use App\Models\Player;
use App\Models\PromotionEarningMethod;
use App\Services\RealWinSolution\RealWinSolution;
use Illuminate\Support\Str;

trait EarnsRewards
{
    /**
     * Get the earnings for the player provided based
     * on the drawing earn criteria
     *
     * @param Player $player Player to track earnings for
     * @param EarningMethodType $type
     * @param bool $startsAt
     * @param bool $endsAt
     * @param string|null $propertyIds Comma delimited string of external property IDs
     * @param PromotionEarningMethod|null $promotionEarningMethod
     * @return float
     * @throws \App\Services\OriginConnector\Exceptions\PlayerEarningTypeUnavailableException
     */
    public function earningsForPlayer(
        Player $player,
        EarningMethodType $type,
        bool $startsAt = false,
        bool $endsAt = false,
        string $propertyIds = null,
        ?PromotionEarningMethod $promotionEarningMethod = null
    ): float
    {
        $starts = $startsAt ? : $this->starts_at;
        $ends = $endsAt ? : $this->ends_at;

        return $player->getEarnedValue($type, $starts, $ends, $propertyIds, $promotionEarningMethod);
    }

    /**
     * Get the earnings by property for the player provided based
     * on the earning method type & drawing earn criteria
     *
     * @param Player $player Player to track earnings for
     * @param EarningMethodType $type Earning method to get earnings for
     * @param bool $startsAt
     * @param bool $endsAt
     * @param string|null $propertyIds Comma delimited string of external property IDs
     * @return float
     * @throws \App\Services\OriginConnector\Exceptions\PlayerEarningTypeUnavailableException
     */
    public function earningsByPropertyForPlayer(
        Player $player,
        EarningMethodType $type,
        bool $startsAt = false,
        bool $endsAt = false,
        ?string $propertyIds = null): float
    {
        $starts = $startsAt ? : $this->starts_at;
        $ends = $endsAt ? : $this->ends_at;

        return $player->getEarnedValueByProperty($type, $starts, $ends, $propertyIds);
    }

    /**
     * Get claimed rewards for this player
     * Note.  There is a column (redeemed) in the pivot table that stores the
     * date and time the reward was redeemed.  It is not the indicator that
     * a reward has been redeemed.  If the pivot table is present, that
     * player has redeemed the reward.
     *
     * @param  Player $Player Player in question.
     * @return Collection
     **/
    public function rewardsClaimedForPlayer(Player$player): Collection
    {
        // Because there are multiple rewards possible we need to map through all of the
        // possible rewards to look for relations to the player in question. After
        // redemptions have been mapped we flatten the collection.
        return $this->rewards()->with('redemptions')->whereHas('redemptions', function ($query) use ($player) {
            $query->where('player_id', '=', $player->id);
        })->get()->pluck('redemptions')->flatten();
    }

    /**
     * Get the rewards available to a player based
     * on the total rewards that have been redeemed,
     * the player's current reward count
     * and the max rewards available
     *
     * @param int $totalEarned Total number of rewards earned by the player
     * @param int $claimedRewardsCount The current number of rewards this player has redeemed
     * @return int
     **/
    public function rewardsAvailable(int $totalEarned, int $claimedRewardsCount): int
    {
        // If you can earn unlimited rewards or have not redeemed all possible
        // rewards then it will pass the difference of the total amount of
        // rewards you have earned minus the rewards you have claimed.
        if ($this->hasUnlimitedRewards() || $totalEarned < $this->max_earnable) {
            return max($totalEarned - $claimedRewardsCount, 0);
        } else {
            // Otherwise return the maximum number of rewards
            // possible minus the amount already claimed.
            return max($this->max_earnable - $claimedRewardsCount, 0);
        }
    }

    /**
     * Get the percent distance the player is
     * from their next earned submission
     *
     * @return integer
     **/
    public function percentToNextReward($valueEarned, PromotionEarningMethod $earningMethod): ?int
    {
        // If we have no earning step criteria then we have no %
        if (is_null($earningMethod->earning_criteria_step_value) and ! Str::contains($earningMethod->type->identifier, 'other')) {
            return 0;
        }

        /**
         * Note: This condition will check if the earning method is from RealWin Solutions
         */
        if (Str::contains($earningMethod->type->identifier, 'other')) {
            if ($valueEarned) {
                return 100;
            }

            return 0;
        }

        /**
         * NOTE : A request was made to allow admins to enter "0" as the earning_criteria_step_value
         * which means the player does not have to earn anything at all and will automatically be
         * given the ability to select rewards up until the "max_earnable" count.
         *
         * This conditional prevents division by zero and returns the "percentage to next rewards" as
         * 100 because the player esentially has earned the next reward already.
         */
        if ($earningMethod->earning_criteria_step_value === 0) {
            return 100;
        } else {
            return floor(($valueEarned % $earningMethod->earning_criteria_step_value) / $earningMethod->earning_criteria_step_value * 100);
        }
    }

    /**
     * Get the number of rewards earned for this player.
     * -----------------------------------------------------
     * In common terms - Get the points/cash/theo value that
     * the player has accumulated and divide it by the amount they need
     * to earn a reward.
     *
     * @param float $valueEarned The value earned by the player to be analyzed
     * @param PromotionEarningMethod $earningMethod
     * @return integer
     **/
    public function rewardsEarned(float $valueEarned, PromotionEarningMethod $earningMethod): float|int
    {
        // If we have no earning step criteria then we have no earnings
        if (is_null($earningMethod->earning_criteria_step_value) and ! Str::contains($earningMethod->type->identifier, 'other')) {
            return 0;
        }

        /**
         * Note: This condition will check if the earning method is from RealWin Solutions
         */
        if (Str::contains($earningMethod->type->identifier, 'other')) {
            if ($valueEarned) {
                return $valueEarned;
            }

            return 0;
        }

        /**
         * NOTE : A request was made to allow admins to enter "0" as the earning_criteria_step_value
         * which means the player does not have to earn anything at all and will automatically be
         * given the ability to select rewards up until the "max_earnable" count.
         *
         * This conditional prevents division by zero and returns the "rewards earned" as the
         * maximum available.
         */
        if ($earningMethod->earning_criteria_step_value === 0) {
            return $this->hasUnlimitedRewards($this->max_earnable) ? $valueEarned : $this->max_earnable;
        } else {
            return floor($valueEarned / $earningMethod->earning_criteria_step_value);
        }
    }

    /**
     * Boolean does this have unlimited rewards?
     * @return boolean
     **/
    public function hasUnlimitedRewards(): bool
    {
        return !$this->max_earnable;
    }
}
