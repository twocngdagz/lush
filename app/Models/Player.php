<?php

namespace App\Models;

use App\Models\Offer\Offer;
use Origin;
use Carbon\Carbon;
use App\PlayerRating;
use App\Traits\HasOriginEarnings;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;


class Player extends Model
{
    use HasOriginEarnings;

    protected $fillable = ['ext_id', 'first_name', 'last_name'];
    protected $table = 'players';

    /**
     * Find or create a player based on the external ID
     *
     * @return Player
     **/
    public static function getFromExternalId($ext_id)
    {
        try {
            return self::where(['ext_id' => $ext_id])->firstOrFail();
        } catch (\Exception $e) {
            \Log::info("Player ($ext_id) not found in kiosk db - attempting to create.");

            return self::createPlayerFromExternalId($ext_id);
        }
    }

    public static function createPlayerFromExternalId($ext_id)
    {
        try {
            $player = Origin::getPlayer($ext_id);
        } catch (\Exception $e) {

            // If this is an excluded player let the caller know.
            if ($e->getCode() == 400) {
                throw new \Exception($e->getMessage(), $e->getCode(), $e);
            }

            \Log::error([
                "Error creating player {$ext_id} in " . __METHOD__,
                $e->getMessage(),
                $e->getFile() . ':' . $e->getLine(),
            ]);

            return false;
        }

        return self::create([
            'ext_id' => trim($player->id),
            'first_name' => $player->profile->first_name,
            'last_name' => $player->profile->last_name,
        ]);
    }

    /**
     * Notifications for this player
     */
    public function notifications()
    {
        return $this->morphMany(KioskNotification::class, 'notifiable')->with('type');
    }

    /**
     * Reward Redemptions
     */
    public function rewardRedemptions()
    {
        return $this->hasMany(RewardRedemption::class);
    }

    /**
     * Get all unread notifications
     */
    public function unreadNotifications()
    {
        return $this->notifications()->unread()->unexpired()
            ->whereHas('promotion', function ($query) {
                $query->active();
            })->with('owner');
    }

    /**
     * Get all unread notifications that do not require pin entry
     */
    public function unreadNoPinNotifications()
    {
        return $this->unreadNotifications()->noPin();
    }

    /**
     * Get property groups
     * @return [type] [description]
     */
    public function propertyGroups(): Collection
    {
        if (appFeatures('global.local-property-groups')) {
            return $this->localPropertyGroups;
        } else {
            return $this->originPropertyGroups();
        }
    }

    /**
     * Get property groups
     * @return [type] [description]
     */
    public function propertyGroupIds(): Collection
    {
        if (appFeatures('global.local-property-groups')) {
            return $this->localPropertyGroupIds();
        } else {
            return $this->originPropertyGroupIds();
        }
    }

    /**
     * Get origin property groups
     * @return array
     */
    public function originPropertyGroups()
    {
        return Origin::getPlayerGroups($this->ext_id);
    }

    /**
     * Get origin property group IDs array
     * @return array
     */
    public function originPropertyGroupIds(): Collection
    {
        return collect(Origin::getPlayerGroups($this->ext_id))->pluck('id');
    }

    /**
     * Get local property groups
     * @return HasManyThrough Relationship
     */
    public function localPropertyGroups()
    {
        /**
         * External player groups
         * @var ExternalPlayerGroup
         */
        return $this->hasManyThrough(PropertyGroup::class, ExternalPlayerGroup::class, 'ext_player_id', 'id', 'ext_id', 'property_group_id');
    }

    /**
     * Get property group IDs array
     * @return array
     */
    public function localPropertyGroupIds()
    {
        return ExternalPlayerGroup::where('ext_player_id', $this->ext_id)->get()->pluck('property_group_id');
    }

    // --------

    /**
     * Make a request for the Origin Player and store
     * the returned first and last name/
     * @return Player
     */
    public function storeOriginName()
    {
        $playerProfile = Origin::getPlayer($this->ext_id)->profile;
        $this->first_name = $playerProfile->first_name;
        $this->last_name = $playerProfile->last_name;
        $this->save();
        return $this;
    }

    /**
     * Does this player have a name stored?
     * @return boolean
     */
    public function hasName()
    {
        return $this->full_name ? true : false;
    }

    /**
     * Get the first name attribute from the Origin connector
     *
     * @return string
     **/
    public function getFullNameAttribute()
    {
        return ($this->first_name || $this->last_name) ? $this->first_name . ' ' . $this->last_name : null;
    }

    public function setFullNameAttribute($name)
    {
        if (!isset($name->first_name) || !isset($name->last_name)) {
            return;
        }
        if ($this->first_name != $name->first_name || $this->last_name != $name->last_name) {
            $this->first_name = $name->first_name;
            $this->last_name = $name->last_name;
            $this->save();
        }
    }

    /**
     * Get the abbreviated name for the player
     *
     * @return string
     **/
    public function getAbbreviatedNameAttribute()
    {
        return $this->first_name . ' ' . substr($this->last_name, 0, 1) . '.';
    }

    /**
     * Get the player rank from the third party
     *
     * @return array
     **/
    public function getRankIdAttribute()
    {
        if (isset($this->attributes['rank_id'])) {
            return $this->attributes['rank_id'];
        }

        return Origin::getPlayer($this->ext_id)->rank->id;
    }

    /**
     * Check third party if player is an employee rank
     *
     * @return boolean
     */
    public function getIsEmployeeAttribute()
    {
        return Origin::getPlayerDetail($this, false)['employee'];
    }

    /**
     * Scope a query to only include a given player
     *
     * @return Builder
     **/
    public function LocalEarningsForTypePropertyAndDateRange($earning_method_type_id = null, $properties = null, $start_at = null, $end_at = null)
    {
        if (!is_array($properties)) {
            $properties = explode(',', $properties);
        }

        return (float)PlayerRating::where('player_id', $this->id)
            ->where('earning_method_type_id', $earning_method_type_id)
            ->whereIn('property_id', $properties)
            ->whereBetween('rating_at', [$start_at, $end_at])
            ->sum('amount');
    }

    /**
     * Check to determine if player has offer already
     *
     * @param $offer_code
     * @return bool
     */
    public function hasOffer($offer_code)
    {
        return appFeatures('global.loyalty-offers.loyalty-offers')
            ? collect(Origin::getPlayerOffers($this->ext_id))->contains('offerCode', $offer_code)
            : collect([]);
    }

    /**
     * Exclude transient rank_id attribute when dirty checking to avoid attempting to save it.
     * @return array
     */
    public function getDirty()
    {
        return Arr::except(parent::getDirty(), ['rank_id']);
    }

    /**
     * Check to determine if player has bucket award already
     *
     * @param $bucket_award_id
     * @return bool
     */
    public function hasBucketAward($bucket_award_id, Carbon $from = null, Carbon $to = null)
    {
        $from = $from ?? now()->subYear();
        $to = $to ?? now()->addDay();

        return appFeatures('global.loyalty-offers.loyalty-offers')
            ? collect(Origin::getPlayerBucketAwards($this->ext_id, $from, $to))->contains('bucketAwardId', $bucket_award_id)
            : collect([]);
    }

    /**
     * Does the player meet the provided criteria?
     *
     * @param array $criteria
     * @param Promotion $promotion
     * @return bool
     * @throws \Exception
     */
    public function meetsCriteria(array $criteria, Promotion $promotion)
    {
        $player_detail = Origin::getPlayerDetail($this, false);
        $startDate = Carbon::parse($player_detail['registered_date']);
        $endDate = now();
        //Log::info('Criteria Player detail: ', $player_detail);

        foreach ($criteria as $item => $val) {
            if (empty($val)) {
                continue;
            }
            switch ($item) {
                // Process in order of least "costly" (DB & CPU) to most.
                // Once a criteria check fails the player does not
                // meet criteria and we return false right away.
                case 'criteria_birth_month':
                    if (!monthsMatch($val, $player_detail['birth_date'])) {
                        return false;
                    }
                    break;
                case 'criteria_minimum_age':
                    if ($val > $player_detail['age']) {
                        return false;
                    }
                    break;
                case 'criteria_maximum_age':
                    if ($val < $player_detail['age']) {
                        return false;
                    }
                    break;
                case 'criteria_gender':
                    // \Log::info("VALUE HERE ".$val. "-" . $player_detail['gender']);
                    // if ($val !== $player_detail['gender']) {
                    //     return false;
                    // }
                    if ($val !== substr($player_detail['gender'], 0, 1)) {
                        return false;
                    }
                    break;
                case 'criteria_new_player':
                    if (($val == 'Y' && $player_detail['days_since_registration'] >= 30) // DY: Replace with account level setting.
                        ||
                        ($val == 'N' && $player_detail['days_since_registration'] < 30)) {
                        return false;
                    }
                    break;
                case 'criteria_max_days_since_enrollment':
                    if ($val < $player_detail['days_since_registration']) {
                        return false;
                    }
                    break;
                case 'criteria_min_days_since_enrollment':
                    if ($val > $player_detail['days_since_registration']) {
                        return false;
                    }
                    break;
                case 'criteria_minimum_point_balance':
                    $points_account = Origin::getPlayerAccountByName($player_detail['ext_id'], Origin::accountPointsName());
                    if ($val > $points_account->amount ?? 0) {
                        return false;
                    }
                    break;
                case 'criteria_minimum_comp_balance':
                    $comp_account = Origin::getPlayerAccountByName($player_detail['ext_id'], Origin::accountCompsName());
                    if ($val > $comp_account->amount ?? 0) {
                        return false;
                    }
                    break;
                case 'criteria_minimum_player_rank_id':
                    $property_ranks = Origin::getPropertyRanks();
                    $rank_detail = (array)($property_ranks->where('id', $player_detail['rank_id'])->first());
                    $tmp = $property_ranks->where('id', $val)->first();
                    if (!$tmp) {
                        \Log::error("Minimum Player Rank ID ($val) not found in Player::meetsCriteria()");
                        \Log::error($property_ranks->toArray());
                        throw new \Exception(500, 'Internal error processing criteria');
                    }
                    if ($tmp->sort_order > $rank_detail['sort_order']) {
                        return false;
                    }
                    break;
                case 'criteria_maximum_player_rank_id':
                    $property_ranks = Origin::getPropertyRanks();
                    $rank_detail = (array)($property_ranks->where('id', $player_detail['rank_id'])->first());
                    $tmp = $property_ranks->where('id', $val)->first();
                    if (!$tmp) {
                        \Log::error("Maximum Player Rank ID ($val) not found in Player::meetsCriteria()");
                        \Log::error($property_ranks->toArray());
                        throw new \Exception(500, 'Internal error processing criteria');
                    }
                    if ($tmp->sort_order < $rank_detail['sort_order']) {
                        return false;
                    }
                    break;
                case 'criteria_tier_points_since_enrollment':
                    if(!appFeatures('promotion.all.criteria-types.tier-points-since-enrollment')) return false;
                    if ($val > Origin::getPlayerPitSlotTierPointsEarned($this->ext_id, Carbon::parse($player_detail['registered_date']), now())) return false;
                    break;
                case 'criteria_points_earned':
                    if ($val > Origin::getPlayerPitSlotPointsEarned($this->ext_id, $promotion->starts_at ?? now(), $promotion->ends_at ?? now())) return false;
                    break;
                case 'criteria_comp_earned':
                    if ($val > Origin::getPlayerPitSlotCompEarned($this->ext_id, $promotion->starts_at ?? now(), $promotion->ends_at ?? now())) return false;
                    break;
                case 'criteria_minimum_rated_play_since_enrollment':
                    if ($val > (Origin::getPlayerPitSlotTimePlayed($this->ext_id, $startDate, $endDate) / 60)) return false;
                    break;
                case 'criteria_minutes_slot_played':
                    if ($val > (Origin::getPlayerSlotTimePlayed($this->ext_id, $promotion->starts_at ?? now(), $promotion->ends_at ?? now()) / 60)) return false;
                    break;
                case 'criteria_minutes_table_played':
                    if ($val > (Origin::getPlayerPitTimePlayed($this->ext_id, $promotion->starts_at ?? now(), $promotion->ends_at ?? now()) / 60)) return false;
                    break;
            }
        }

        // We made it through all the checks.
        return true;
    }


    /**
     * Does the player meet the provided criteria?
     *
     * @param array $criteria
     * @param Promotion $promotion
     * @return bool
     * @throws \Exception
     */
    public function meetsCriteriaForOffer(array $criteria, Offer $offer)
    {
        $player_detail = Origin::getPlayerDetail($this, false);
        $startDate = Carbon::parse($player_detail['registered_date']);
        $endDate = now();

        foreach ($criteria as $item => $val) {
            if (empty($val)) {
                continue;
            }
            switch ($item) {
                // Process in order of least "costly" (DB & CPU) to most.
                // Once a criteria check fails the player does not
                // meet criteria and we return false right away.
                case 'criteria_birth_month':
                    if (!monthsMatch($val, $player_detail['birth_date'])) {
                        return false;
                    }
                    break;
                case 'criteria_minimum_age':
                    if ($val > $player_detail['age']) {
                        return false;
                    }
                    break;
                case 'criteria_maximum_age':
                    if ($val < $player_detail['age']) {
                        return false;
                    }
                    break;
                case 'criteria_gender':
                    if ($val !== $player_detail['gender']) {
                        return false;
                    }
                    break;
                case 'criteria_new_player':
                    if (($val == 'Y' && $player_detail['days_since_registration'] >= 30) // DY: Replace with account level setting.
                        ||
                        ($val == 'N' && $player_detail['days_since_registration'] < 30)) {
                        return false;
                    }
                    break;
                case 'criteria_max_days_since_enrollment':
                    if ($val < $player_detail['days_since_registration']) {
                        return false;
                    }
                    break;
                case 'criteria_min_days_since_enrollment':
                    if ($val > $player_detail['days_since_registration']) {
                        return false;
                    }
                    break;
                case 'criteria_minimum_point_balance':
                    $points_account = Origin::getPlayerAccountByName($player_detail['ext_id'], Origin::accountPointsName());
                    if ($val > $points_account->amount ?? 0) {
                        return false;
                    }
                    break;
                case 'criteria_minimum_comp_balance':
                    $comp_account = Origin::getPlayerAccountByName($player_detail['ext_id'], Origin::accountCompsName());
                    if ($val > $comp_account->amount ?? 0) {
                        return false;
                    }
                    break;
                case 'criteria_minimum_player_rank_id':
                    $property_ranks = Origin::getPropertyRanks();
                    $rank_detail = (array)($property_ranks->where('id', $player_detail['rank_id'])->first());
                    $tmp = $property_ranks->where('id', $val)->first();
                    if (!$tmp) {
                        \Log::error("Minimum Player Rank ID ($val) not found in Player::meetsCriteria()");
                        \Log::error($property_ranks->toArray());
                        throw new \Exception(500, 'Internal error processing criteria');
                    }
                    if ($tmp->sort_order > $rank_detail['sort_order']) {
                        return false;
                    }
                    break;
                case 'criteria_maximum_player_rank_id':
                    $property_ranks = Origin::getPropertyRanks();
                    $rank_detail = (array)($property_ranks->where('id', $player_detail['rank_id'])->first());
                    $tmp = $property_ranks->where('id', $val)->first();
                    if (!$tmp) {
                        \Log::error("Maximum Player Rank ID ($val) not found in Player::meetsCriteria()");
                        \Log::error($property_ranks->toArray());
                        throw new \Exception(500, 'Internal error processing criteria');
                    }
                    if ($tmp->sort_order < $rank_detail['sort_order']) {
                        return false;
                    }
                    break;
                case 'criteria_tier_points_since_enrollment':
                    if(!appFeatures('promotion.all.criteria-types.tier-points-since-enrollment')) return false;
                    if ($val > Origin::getPlayerPitSlotTierPointsEarned($this->ext_id, Carbon::parse($player_detail['registered_date']), now())) return false;
                    break;
                case 'criteria_points_earned':
                    if ($val > Origin::getPlayerPitSlotPointsEarned($this->ext_id, $offer->start_date ?? now(), $offer->end_date ?? now())) return false;
                    break;
                case 'criteria_comp_earned':
                    if ($val > Origin::getPlayerPitSlotCompEarned($this->ext_id, $offer->start_date ?? now(), $offer->end_date ?? now())) return false;
                    break;
                case 'criteria_minimum_rated_play_since_enrollment':
                    if ($val > (Origin::getPlayerPitSlotTimePlayed($this->ext_id, $startDate, $endDate) / 60)) return false;
                    break;
                case 'criteria_minutes_slot_played':
                    if ($val > (Origin::getPlayerSlotTimePlayed($this->ext_id, $offer->start_date ?? now(), $offer->end_date ?? now()) / 60)) return false;
                    break;
                case 'criteria_minutes_table_played':
                    if ($val > (Origin::getPlayerPitTimePlayed($this->ext_id, $offer->start_date ?? now(), $offer->end_date ?? now()) / 60)) return false;
                    break;
            }
        }

        // We made it through all the checks.
        return true;
    }

    /**
     * Check if bounce back promotion meets
     */
    public function bounceBackEligible(BounceBackPromotion $bounceBackPromotion)
    {
        $redeemedRewards = $this->rewardRedemptions()
            ->whereBetween('created_at', [$bounceBackPromotion->redemption_starts_at, $bounceBackPromotion->redemption_ends_at])
            ->pluck('reward_id')
            ->all();

        Log::info('redeemed rewards', $redeemedRewards);

        if (!$redeemedRewards) {
            return false;
        }

        $hasRedeemption = Reward::whereIn('id', $redeemedRewards)
            ->where('promotion_id', $bounceBackPromotion->previous_promotion_id)
            ->exists();

        Log::info('Bounce Back Promotion For: ' . $bounceBackPromotion->promotion->name);
        Log::info('Has Redemption: ' . $hasRedeemption);

        return $hasRedeemption;
    }
}
