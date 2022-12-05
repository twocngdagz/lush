<?php

/**
 * This API trait contains functionality for property related requests from
 * the Phi Mock CMS API.
 */

namespace App\Services\OriginConnector\Providers\PhiMock\Traits;

use App\Services\OriginConnector\ConnectionException;
use App\Services\OriginConnector\Exceptions\PropertyNotFoundException;
use App\Services\OriginConnector\Providers\PhiMock\Transformers\PropertyInfoTransformer;
use App\Services\OriginConnector\Providers\PhiMock\Transformers\PropertyRankTransformer;
use App\Services\OriginConnector\Providers\PhiMock\Transformers\PropertyGroupTransformer;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

trait PropertyTrait
{
    /**
     * Get available property information
     *
     * @param int|string|null $propertyId (ignored - single-property)
     * @return array
     */
    public function getPropertyInfo($propertyId = null)
    {
        return $this->remember("getPropertyInfo", function () {
            try {
                $properties = collect($this->get("/properties"));

                // Make sure we have at least one property
                if (!isset($properties[0])) {
                    throw new PropertyNotFoundException;
                }

                return transformify($properties->firstWhere('active', true), new PropertyInfoTransformer);
            } catch (\Exception $e) {
                switch ($e->getCode()) {
                    case 404:
                        throw new PropertyNotFoundException;
                    default:
                        Log::error(["Error calling getPropertyInfo", $e->getFile(), $e->getLine(), $e->getMessage()]);
                        throw new ConnectionException("There was an error accessing the property information data.", $e->getCode());
                }
            }
        });
    }

    /**
     * Get all groups for the authenticated property.
     *
     * @param int|string|null $search ID or Name of specific group
     * @return Illuminate\Support\Collection|stdClass
     **/
    public function getPropertyGroups($search = null)
    {
        return $this->remember("getPropertyGroups:$search", function () use ($search) {
            try {
                $request = "/groups";
                $result = $this->get($request);

                return collect(transformify(collect($result->data), new PropertyGroupTransformer));
            } catch (\Exception $e) {
                switch ($e->getCode()) {
                    case 404:
                        // Return empty collection if expecting collection otherwise return null
                        return (empty($search)) ? collect([]) : null;
                        break;
                    default:
                        Log::error(["Error calling getPropertyGroups", $e->getFile(), $e->getLine(), $e->getMessage()]);
                        throw new ConnectionException('There was an error accessing the property groups.', $e->getCode());
                }
            }
        });
    }

    /**
     * Get all calculations for a property
     *
     * @return Illuminate\Support\Collection
     */
    public function getPropertyRanks()
    {
        return $this->remember("getPropertyRanks", function () {
            try {
                $response = $this->get("/ranks");

                return collect(transformify(collect($response->data), new PropertyRankTransformer));
            } catch (\Exception $e) {
                Log::error(["Error calling getPropertyRanks", $e->getFile(), $e->getLine(), $e->getMessage()]);
                throw new ConnectionException("There was an error accessing the property ranks.", $e->getCode());
            }
        });
    }

    /**
     * Get all property account types with supported
     * transaction types for each.
     *
     * @param int|string|null $search
     * @return Illuminate\Support\Collection
     */
    public function getPropertyAccountTransactionTypes($search = null)
    {
        return collect();
    }

    /**
     * Get all property transaction types
     *
     * @param int|string|null $search
     * @return Illuminate\Support\Collection
     **/
    public function getPropertyTransactionTypes($search = null)
    {
        return collect();
    }

    /**
     * Get all redemption types.
     *
     * @param int|string|null $search
     * @return Illuminate\Support\Collection
     */
    public function getPropertyRedemptionTypes($search = null)
    {
        return collect();
    }

    /**
     * Get all prize types.
     *
     * @param string|null $search
     * @return Illuminate\Support\Collection
     */
    public function getPropertyPrizeTypes($search = null)
    {
        return collect();
    }

    /**
     * Get all account, transaction, redemption, and
     * prize types and return the results together.
     *
     * @return Illuminate\Support\Collection
     */
    public function getPropertyRedemptionAccountLists()
    {
        return collect([]);
    }

    /**
     * Get the most recent PropertyRedemptionAccountSettings settings array.
     *
     * @param int $propertyId
     * @return array
     */
    public function getPropertyRedemptionAccountSettings(int $propertyId)
    {
        return [];
    }

    /**
     * Get the current PropertyRedemptionAccountSettings
     * and include the account type, transaction type,
     * redemption type, and prize type lists.
     *
     * @param int     $propertyId
     * @param Request $request
     * @return array
     */
    public function getPropertyRedemptionAccountOptions(int $propertyId, Request $request)
    {
        return [];
    }

    /**
     * Validate and store form data for PropertyRedemptionAccountOptions.
     *
     * @param int     $propertyId
     * @param Request $request
     * @return void
     */
    public function updatePropertyRedemptionAccountOptions(int $propertyId, Request $request)
    {
    }

    /**
     * Get the amount of points needed to make a dollar
     *
     * @return float
     * @throws ConnectionException
     */
    public function getPropertyPointsPerDollar()
    {
        // We need to add a setting to the mock CMS for this and
        // expose it via an API endpoint. For now we will just
        // hard-code it to 100 points per dollar.
        // 
        return 100;
//        throw new ConnectionException("Could not get ratio to convert points balance to dollars.");
    }

    /**
     * Get the amount of comps needed to make a dollar
     *
     * @return float
     * @throws ConnectionException
     */
    public function getPropertyCompsPerDollar()
    {
        throw new ConnectionException("Could not get ratio to convert comps balance to dollars.");
    }

    /**
     * Get a list of table games
     *
     * @return Illuminate\Support\Collection
     */
    public function getTableGames()
    {
        return collect();
    }

    /**
     * Get a list of Address Types
     *
     * @return Illuminate\Support\Collection
     */
    public function getAddressTypes()
    {
        return collect();
    }

}
