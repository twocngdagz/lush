<?php

namespace App\Services\OriginConnector\Providers\Lush\Traits;

use App\Services\OriginConnector\Providers\Lush\Models\LushRank;
use App\Services\OriginConnector\Providers\Lush\Models\LushGroup;
use App\Services\OriginConnector\ConnectionException;
use App\Services\OriginConnector\Exceptions\PropertyNotFoundException;
use App\Services\OriginConnector\Providers\PhiMock\Transformers\PropertyGroupTransformer;
use App\Services\OriginConnector\Providers\PhiMock\Transformers\PropertyInfoTransformer;
use App\Services\OriginConnector\Providers\PhiMock\Transformers\PropertyRankTransformer;
use Illuminate\Support\Facades\Log;

trait PropertyTrait
{
    public function getPropertyGroups($search = null)
    {
        return $this->remember("getLushPropertyGroups:$search", function () use ($search) {
            try {
                return collect(transformify(collect(LushGroup::all()), new PropertyGroupTransformer));
            } catch (\Exception $e) {
                Log::error(["Error calling getPropertyGroups", $e->getFile(), $e->getLine(), $e->getMessage()]);
                throw new ConnectionException('There was an error accessing the property groups.', $e->getCode());
            }
        });
    }

    public function getPropertyInfo($propertyId = null)
    {
        return $this->remember("getLushPropertyInfo", function () {
            try {
                $properties = collect([
                    json_decode('{"id":1,"name":"Lush Player Management","active":true,"timezone":"America\/Los_Angeles"}', false)
                ]);

                return transformify($properties->firstWhere('active', true), new PropertyInfoTransformer);
            } catch (\Exception $e) {
                Log::error(["Error calling getPropertyInfo", $e->getFile(), $e->getLine(), $e->getMessage()]);
            }
        });
    }

    public function getPropertyRanks()
    {
        return $this->remember("getLushPropertyRanks", function () {
            try {
                return collect(transformify(LushRank::all(), new PropertyRankTransformer));
            } catch (\Exception $e) {
                Log::error(["Error calling getPropertyRanks", $e->getFile(), $e->getLine(), $e->getMessage()]);
                throw new ConnectionException("There was an error accessing the property ranks.", $e->getCode());
            }
        });
    }

    public function getPropertyTransactionTypes($search = null)
    {
        return collect();
    }

    public function getPropertyPointsPerDollar()
    {
        // We need to add a setting to the mock CMS for this and
        // expose it via an API endpoint. For now we will just
        // hard-code it to 100 points per dollar.
        //
        return 100;
//        throw new ConnectionException("Could not get ratio to convert points balance to dollars.");
    }
}
