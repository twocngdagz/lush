<?php

namespace App\Services\OriginConnector\Contracts;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use App\Providers\AppServiceProvider;

/**
 * This is the base connector for a new 3rd party "Origin Connector".
 * The new connector must extend this connector contract.
 */
interface ConnectorContract extends PlayerContract, PropertyContract {

    /**
     * Should return the cache tag to use for all requests
     * @return string
     */
    public function cacheTag(): string;

    /**
     * Should return the cache ID prefix to use when storing cached data
     * @param string $appends The cache ID string to append to the default
     * @return string
     */
    public function cacheId(string $appends): string;

    /**
     * The connector's uri.  This is used to concatenate
     * a base IP or URL with the provided path.
     * @param  string $path URI path
     * @return string Full url
     */
    public function uri($path = ''): string;

    /**
     * Returns the name of the Points account according to the
     * third party data
     * @return string
     */
    public function accountPointsName(): string;

    /**
     * Returns the comps account name according to
     * the third party data
     * @return string
     */
    public function accountCompsName(): string;

    /**
     * Returns the promo account name
     * according to third party data.
     * @return [type] [description]
     */
    public function accountPromoName(): string;

    /**
     * Return the entire list of supported features (array).
     *
     * @return array
     */
    function supportedFeaturesList(): array;

    /**
     * Display connector connection settings
     */
    public function connectionSettingsIndex();

    /**
     * Store connector connection settings
     */
    public function connectionSettingsUpdate(Request $request);

    /**
     * Returns a collection of points earned daily for x number of days
     */
    public function getPlayerGamingActivityReportGraph($extPlayerId, $days);

    /**
     * Returns a string indicating the version of the CMS API
     * the connector is currently working with if it can be
     * retrieved. Otherwise NULL is returned.
     *
     * @return string
     */
    public function apiVersion(): string;

}
