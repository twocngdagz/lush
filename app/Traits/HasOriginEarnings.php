<?php

namespace App\Traits;

use App\Models\EarningMethodType;

use App\Models\PromotionEarningMethod;
use App\Models\Property;
use App\Services\OriginConnector\Exceptions\PlayerEarningTypeUnavailableException;

use Carbon\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

/**
 * This trait encapsulates the earning methods available to the player model.
 */
trait HasOriginEarnings
{

    /**
     * Get earnings value based on earning method
     * type and date range
     * @param EarningMethodType $type
     * @param Carbon|null $start
     * @param Carbon|null $end
     * @param string|null $propertyIds Comma delimited string of external property IDs
     * @return integer
     * @throws PlayerEarningTypeUnavailableException
     */
    public function getEarnedValue(EarningMethodType $type, Carbon $start = null, Carbon $end = null, string $propertyIds = null, PromotionEarningMethod $promotionEarningMethod =  null)
    {
        try {
            if (is_null($type->origin_game_code)) {
                // Not poker
                $method = 'getPlayer' . Str::studly($type->identifier);
                $result = call_user_func(
                    [Origin::class, $method],
                    $this->ext_id,
                    $start,
                    $end,
                    $propertyIds,
                    $promotionEarningMethod
                );
                Log::info('HasOriginEarnings::getEarnedValue() - calls ' . Origin::class . '::' . $method . ' - ' . $start . ' to ' . $end . ' - Result: ' . $result);
                return $result;
            } else {
                // Poker
                $propertyGameCodeMap = Property::whereIn('ext_property_id', explode(',', $propertyIds))
                    ->with('pokerRatings')
                    ->get()
                    ->mapWithKeys(function ($property) {
                        return [$property->property_code => $property->pokerRatings->pluck('origin_game_code')];
                    })->toArray();

                return call_user_func(
                    [Origin::class, 'getPlayer' . Str::studly($type->identifier)],
                    $this->ext_id,
                    $start,
                    $end,
                    $propertyGameCodeMap,
                    $propertyIds
                );
            }

        } catch (\Exception $e) {
            throw new PlayerEarningTypeUnavailableException($e->getMessage(), $e->getCode(), $e);
        }
    }

    /**
     * Get earnings value based on earning method
     * type and date range keyed by property id
     * @param EarningMethodType $type
     * @param Carbon|null $start
     * @param Carbon|null $end
     * @param string|null $propertyIds Comma delimited string of external property IDs
     * @return integer
     * @throws PlayerEarningTypeUnavailableException
     */
    public function getEarnedValueByProperty(EarningMethodType $type, Carbon $start = null, Carbon $end = null, string $propertyIds = null): ?int
    {
        try {
            if (is_null($type->origin_game_code)) {
                return call_user_func(
                    [Origin::class, 'getPlayer' . Str::studly($type->identifier) . 'ByProperty'],
                    $this->ext_id,
                    $start,
                    $end,
                    $propertyIds
                );
            } else {
                return call_user_func(
                    [Origin::class, 'getPlayer' . Str::studly($type->identifier) . 'ByProperty'],
                    $this->ext_id,
                    $start,
                    $end,
                    $type->origin_game_code,
                    $propertyIds
                );
            }

        } catch (\Exception $e) {
            throw new PlayerEarningTypeUnavailableException();
        }
    }
}
