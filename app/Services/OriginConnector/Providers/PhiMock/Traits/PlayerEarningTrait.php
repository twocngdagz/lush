<?php

/**
 * This API trait contains functionality related to player earnings for the
 * Phi Mock API.
 */

namespace App\Services\OriginConnector\Providers\PhiMock\Traits;

use App\Player;
use App\Services\OriginConnector\ConnectionException;
use App\Services\OriginConnector\Providers\PhiMock\Transformers\TrackingSessionTransformer;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use GuzzleHttp\Exception\GuzzleException;

trait PlayerEarningTrait
{

    /**
     * Get tracking for a given player ID
     *
     * @param        $extPlayerId
     * @param Carbon $startDate A carbon instance of the start date
     * @param Carbon $endDate   A carbon instance of the end date
     * @return Illuminate\Support\Collection
     */
    public function getPlayerActivity($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        // If we're on a connector that uses local earnings then we'll
        // collect earnings from the player_ratings table. Otherwise
        // we'll collect earnings via the Phi Mock API.
        if (!config('origin.use_local_ratings', false)) {
            return collect($this->getPlayerTracking($extPlayerId, $startDate, $endDate));
        }

        \Log::info("getPlayerActivity - Player ID: $extPlayerId for period " . $startDate . " thru " . $endDate);
        $cacheId = 'getPlayerActivity:' . $extPlayerId . ':' . $startDate->format('YmdHi') . ':' . $endDate->format('YmdHi');

        return $this->rememberPlayer($cacheId, function () use ($extPlayerId, $startDate, $endDate) {
            $endDate = $endDate ?: Carbon::now();

            // Get the local player.
            $player = Player::getFromExternalId($extPlayerId);

            $ratings = DB::table('player_rating_details as prd')
                ->join('player_ratings as pr', 'prd.player_rating_id', '=', 'pr.id')
                ->join('earning_method_types as emt', 'prd.earning_method_type_id', '=', 'emt.id')
                ->select(
                    'pr.ext_rating_id',
                    'pr.rating_at',
                    'prd.earning_method_type_id',
                    'emt.identifier as earning_method_type_identifier',
                    'prd.amount'
                )
                ->where('pr.player_id', $player->id)
                ->whereBetween('pr.rating_at', [$startDate, $endDate])
                ->where('prd.amount', '>', 0)
                ->orderBy('pr.ext_rating_id')
                ->orderBy('pr.rating_at')
                ->orderBy('prd.earning_method_type_id')
                ->get();

            // TODO: Create a transform function to match OLKG GetPlayerGamingActivityEx data.
            return $ratings;
        });
    }

    /**
     * Get tracking for a given player ID
     *
     * @param mixed  $extPlayerId
     * @param Carbon $startDate If not passed or null, tracking for all time
     * @param Carbon $endDate
     * @param string $statType  SLOT, PIT, or null
     * @return Illuminate\Support\Collection
     **/
    public function getPlayerTracking($extPlayerId, Carbon $startDate = null, Carbon $endDate = null, string $statType = null)
    {
        $cacheId = "getPlayerTracking:$extPlayerId";
        if ($startDate) {
            $cacheId .= ":" . $startDate->format('Y-m-d\TH:i:s') . ":" . $endDate->format('Y-m-d\TH:i:s');
        }
        if ($statType) {
            $cacheId .= ":$statType";
        }

        return $this->rememberPlayer($cacheId, function () use ($extPlayerId, $startDate, $endDate, $statType) {
            $urlBase = "/players/$extPlayerId/ratings";
            $urlQuery = '';
            $searchParams = [];
            if ($startDate && $endDate) {
                $searchParams[] = 'filter[play_between]=' . $startDate->format('Y-m-d\TH:i:s')
                    . ',' . $endDate->format('Y-m-d\TH:i:s');
            }
            if ($statType) {
                $searchParams[] = 'filter[play_type]=' . $statType;
            }
            if (count($searchParams) > 0) {
                $urlQuery = implode('&', $searchParams);
            }
            $trackingData = [];

            $result = $this->get("$urlBase?$urlQuery");
            while (true) {
                try {
                    $trackingData = array_merge($trackingData, $result->data);

                    $nextPage = $result->current_page + 1;
                    $result = $this->get("$urlBase?$urlQuery&page=$nextPage");
                    if (!$result->data) {
                        break 1;
                    }
                } catch (GuzzleException $e) {
                    if ($e->getCode() == 404) {
                        break 1;
                    }
                    switch ($e->getCode()) {
                        default:
                            throw new ConnectionException('There was an error accessing player tracking.', $e->getCode());
                    }
                }
            }

            // filter for only the time period we want.
            $trackingData = collect($trackingData);

            return collect(transformify($trackingData, new TrackingSessionTransformer));
        });
    }

    /**
     * Get the slot points earned over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    function getPlayerSlotPointsEarned($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'slot-points-earned')->sum('amount');
        }

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate, 'slot')->sum('points_earned');
    }

    /**
     * Get the pit points earned over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getPlayerPitPointsEarned($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'pit-points-earned')->sum('amount');
        }

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate, 'pit')->sum('points_earned');
    }

    /**
     * Get the pit + slot points earned over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getPlayerPitSlotPointsEarned($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->whereIn('earning_method_type_identifier', ['slot-points-earned', 'pit-points-earned'])->sum('amount');
        }

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate)->sum('points_earned');
    }

    /**
     * Get the slot cash in over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getPlayerSlotCashIn($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'slot-cash-in')->sum('amount');
        }

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate, 'slot')->sum('cash_in');
    }

    /**
     * Get the pit cash in over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getPlayerPitCashIn($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'pit-cash-in')->sum('amount');
        }

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate, 'pit')->sum('cash_in');
    }

    /**
     * Get the pit + slot cash in over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getPlayerPitSlotCashIn($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->whereIn('earning_method_type_identifier', ['slot-cash-in', 'pit-cash-in'])->sum('amount');
        }

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate)->sum('cash_in');
    }

    /**
     * Get the slot theo over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getPlayerSlotTheoWin($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'slot-theo-win')->sum('amount');
        }

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate, 'slot')->sum('theo_win');
    }

    /**
     * Get the pit theo win over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getPlayerPitTheoWin($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'pit-theo-win')->sum('amount');
        }

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate, 'pit')->sum('theo_win');
    }

    /**
     * Get the pit + slot theo win over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getPlayerPitSlotTheoWin($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->whereIn('earning_method_type_identifier', ['slot-theo-win', 'pit-theo-win'])->sum('amount');
        }

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate)->sum('theo_win');
    }

    /**
     * Get the slot actual win over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getPlayerSlotActualWin($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'slot-actual-win')->sum('amount');
        }

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate, 'slot')->sum('actual_win');
    }

    /**
     * Get the pit actual win over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getPlayerPitActualWin($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'pit-actual-win')->sum('amount');
        }

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate, 'pit')->sum('actual_win');
    }

    /**
     * Get the pit + slot actual win over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getPlayerPitSlotActualWin($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->whereIn('earning_method_type_identifier', ['pit-actual-win', 'slot-actual-win'])->sum('amount');
        }

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate)->sum('actual_win');
    }

    /**
     * Get the slot comp earned over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getPlayerSlotCompEarned($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'slot-comp-earned')->sum('amount');
        }

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate, 'slot')->sum('comp_earned');
    }

    /**
     * Get the pit comp earned over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getPlayerPitCompEarned($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'pit-comp-earned')->sum('amount');
        }

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate, 'pit')->sum('comp_earned');
    }

    /**
     * Get the pit + slot comp earned over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getPlayerPitSlotCompEarned($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->whereIn('earning_method_type_identifier', ['slot-comp-earned', 'pit-comp-earned'])->sum('amount');
        }

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate)->sum('comp_earned');
    }

    /**
     * Get the poker points earned over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @param null        $propertyGameCodeMap
     * @return float
     */
    public function getPlayerPokerPointsEarned($extPlayerId, Carbon $startDate = null, Carbon $endDate = null, $propertyGameCodeMap = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'poker-points-earned')->sum('amount');
        }
    }

    /**
     * Get the poker cash in over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @param null        $propertyGameCodeMap
     * @return float
     */
    public function getPlayerPokerCashIn($extPlayerId, Carbon $startDate = null, Carbon $endDate = null, $propertyGameCodeMap = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'poker-cash-in')->sum('amount');
        }
    }

    /**
     * Get the poker theo win over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @param null        $propertyGameCodeMap
     * @return float
     */
    public function getPlayerPokerTheoWin($extPlayerId, Carbon $startDate = null, Carbon $endDate = null, $propertyGameCodeMap = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'poker-theo-win')->sum('amount');
        }
    }

    /**
     * Get the poker actual win over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @param null        $propertyGameCodeMap
     * @return float
     */
    public function getPlayerPokerActualWin($extPlayerId, Carbon $startDate = null, Carbon $endDate = null, $propertyGameCodeMap = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'poker-actual-win')->sum('amount');
        }
    }

    /**
     * Get the poker comp earned over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @param null        $propertyGameCodeMap
     * @return float
     */
    public function getPlayerPokerCompEarned($extPlayerId, Carbon $startDate = null, Carbon $endDate = null, $propertyGameCodeMap = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'poker-comp-earned')->sum('amount');
        }
    }

    /**
     * Get the slot time played over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getPlayerSlotTimePlayed($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        // We are not storing time played in local ratings so force the query to the API
        $currentLocalRatingsSetting = config('origin.use_local_ratings');
        config(['origin.use_local_ratings' => false]);
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'slot-time-played')->sum('amount');
        }
        config(['origin.use_local_ratings' => $currentLocalRatingsSetting]);

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate, 'slot')->sum('time_played');
    }

    /**
     * Get the pit time played over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getPlayerPitTimePlayed($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        // We are not storing time played in local ratings so force the query to the API
        $currentLocalRatingsSetting = config('origin.use_local_ratings');
        config(['origin.use_local_ratings' => false]);
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'pit-time-played')->sum('amount');
        }

        config(['origin.use_local_ratings' => $currentLocalRatingsSetting]);

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate, 'pit')->sum('time_played');
    }

    /**
     * Get the pit + slot time played over a period of time
     * or over the length of all tracking
     *
     * @param             $extPlayerId
     * @param Carbon|null $startDate
     * @param Carbon|null $endDate
     * @return float
     */
    public function getPlayerPitSlotTimePlayed($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        // We are not storing time played in local ratings so force the query to the API
        $currentLocalRatingsSetting = config('origin.use_local_ratings');
        config(['origin.use_local_ratings' => false]);
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'pit-slot-time-played')->sum('amount');
        }

        config(['origin.use_local_ratings' => $currentLocalRatingsSetting]);

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate)->sum('time_played');
    }
}
