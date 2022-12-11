<?php

namespace App\Services\OriginConnector;



use App\Services\OriginConnector\Contracts\ConnectorContract;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

/**
 * This is the default extendable connector for third parties into the Loyalty System
 * Each third party data connector will need to extend this file and implement
 * the required contracts.
 */
abstract class Connector implements ConnectorContract
{

    use Traits\OriginPlayerTrait;
    use Traits\ConnectionSettingsTrait;

    /**
     * Connection timeout in seconds
     * @var float
     */
    protected float $timeout = 10.0;

    /**
     * The base URL for the connector
     * @var string
     */
    protected string $baseUrl;

    /**
     * Connector Base Path
     * @var string
     */
    protected string $basePath;

    /**
     * Cache instance to use for requests
     */
    public $cache;

    /**
     * Cache enable or disable
     *
     * @var boolean
     */
    public bool $cacheEnabled = false;

    /**
     * The number of minutes to store data in the cache
     *
     * @var integer
     */
    public int $cacheMinutes = 1;

    /**
     * Cache player data enable or disable
     *
     * @var boolean
     */
    public bool $cachePlayerEnabled = false;

    /**
     * The number of minutes to store player data in the cache
     *
     * @var integer
     */
    public int $cachePlayerMinutes = 1;

    /**
     * Activity Requests can be tracked to not perform
     * double requests back-to-back when getting multiple
     * earning account types.
     *
     * @var array
     */
    public array $playerActivityRequests = [];

    /**
     * Get the connector version string
     *
     * @return string Version Identifier string
     */
    public function version(): string
    {
        return env('ORIGIN_CONNECTOR_IDENTIFIER');
    }

    /**
     * Return the entire list of supported features (array).
     * Each connector must implement this method to supply
     * the list of features the connector supports.
     *
     * @return array|bool
     */
    abstract function supportedFeaturesList(): array|bool;

    /**
     * Return the entire list of supported features (array) or
     * a specific feature if the $feature parameter is used.
     *
     * @return array/boolean - Returns an array or a boolean
     */
    public function supportedFeatures($feature = ''): array|bool
    {
        if (strlen($feature) == 0) {
            return $this->supportedFeaturesList();
        }

        return Arr::get($this->supportedFeaturesList(), $feature);
    }

    /**
     * Set the connection timeout
     *
     * @return void
     **/
    public function setTimeout($seconds): void
    {
        $this->timeout = $seconds;
    }

    /**
     * Build the uri path for all non-authorization
     * calls to the origin api.
     *
     * @param string $path The path to append to the API URI
     * @return string
     **/
    public function uri($path = ''): string
    {
        return $this->basePath . $path;
    }

    /**
     * Cache tag name for tagging requests through the origin connector
     *
     * @return string
     */
    public function cacheTag(): string
    {
        return 'origin-data';
    }

    /**
     * Cache tag name for tagging player data requests through the origin connector
     *
     * @return string
     */
    public function cachePlayerTag(): string
    {
        return 'origin-player-data';
    }

    /**
     * String based cache ID for referencing
     * and storing cached request data
     *
     * @param string $append Cache data identifier
     * @return string
     */
    public function cacheId(string $append): string
    {
        return 'origin:requests:' . $append;
    }

    /**
     * Make a request but filter it through caching first.
     *
     * @param string $cacheId unique cache ID to append to the default cache ID defined by the connector
     * @param integer $minutes number of minutes to cache the request for
     * @param Closure $callback
     * @return collection
     */
    public function remember(string $cacheId, Closure $callback): Collection
    {
        if ($this->cacheEnabled) {
            return $this->cache->tags($this->cacheTag())->remember($this->cacheId($cacheId), now()->addMinutes($this->cacheMinutes), $callback);
        } else {
            return cache()->store('array')->rememberForever($cacheId, $callback);
        }
    }

    /**
     * Make a request but filter it through caching first.
     *
     * @param string $cacheId unique cache ID to append to the default cache ID defined by the connector
     * @param integer $minutes number of minutes to cache the request for
     * @param Closure $callback
     * @return collection
     */
    public function rememberPlayer(string $cacheId, Closure $callback): Collection
    {
        if ($this->cachePlayerEnabled) {
            return $this->cache->tags($this->cachePlayerTag())->remember($this->cacheId($cacheId), now()->addMinutes($this->cachePlayerMinutes), $callback);
        } else {
            return cache()->store('array')->rememberForever($cacheId, $callback);
        }
    }

    /**
     * Clear a cached value
     *
     * @param string $cacheId unique cache ID to append to the default cache ID defined by the connector
     */
    public function forget(string $cacheId): void
    {
        if ($this->cacheEnabled) {
            $this->tags($this->cacheTag())->forget($this->cacheId($cacheId));
        } else {
            cache()->store('array')->forget($cacheId);
        }
    }

}
