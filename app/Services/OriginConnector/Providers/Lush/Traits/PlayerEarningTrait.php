<?php

namespace App\Services\OriginConnector\Providers\Lush\Traits;

use App\Services\OriginConnector\Providers\Lush\Models\LushRating;
use App\Player;
use App\Services\OriginConnector\ConnectionException;
use App\Services\OriginConnector\Providers\PhiMock\Transformers\TrackingSessionTransformer;
use Carbon\Carbon;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Support\Facades\DB;

trait PlayerEarningTrait
{
    public function getPlayerTracking(int $extPlayerId, ?Carbon $startDate = null, ?Carbon $endDate = null, ?string $statType = null): array
    {
        $cacheId = "getLushPlayerTracking:$extPlayerId";
        if ($startDate) {
            $cacheId .= ":" . $startDate->format('Y-m-d\TH:i:s') . ":" . $endDate->format('Y-m-d\TH:i:s');
        }
        if ($statType) {
            $cacheId .= ":$statType";
        }

        return $this->rememberPlayer($cacheId, function () use ($extPlayerId, $startDate, $endDate, $statType) {
            try {
                $trackingData = LushRating::query()
                    ->where('play_type', $statType)
                    ->where('lush_player_id', $extPlayerId)
                    ->PlayBetween($startDate->format('Y-m-d\TH:i:s'), $endDate->format('Y-m-d\TH:i:s'))->get();
                return collect(transformify($trackingData, new TrackingSessionTransformer));
            } catch (\Exception $e) {
                throw new ConnectionException('There was an error accessing player tracking.', $e->getCode());
            }
        });
    }

    function getPlayerSlotPointsEarned($extPlayerId, Carbon $startDate = null, Carbon $endDate = null)
    {
        if (config('origin.use_local_ratings')) {
            $ratings = $this->getPlayerActivity($extPlayerId, $startDate, $endDate);

            return (float)$ratings->where('earning_method_type_identifier', 'slot-points-earned')->sum('amount');
        }

        return $this->getPlayerTracking($extPlayerId, $startDate, $endDate, 'slot')->sum('points_earned');
    }

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
}
