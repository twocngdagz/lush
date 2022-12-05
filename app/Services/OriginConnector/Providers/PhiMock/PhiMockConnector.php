<?php

namespace App\Services\OriginConnector\Providers\PhiMock;

use App\Account;
use App\Services\OriginConnector\Connector;

use App\Services\RealWinSolution\Traits\RealWinPlayerEarningTrait;
use GuzzleHttp\Client as Guzzle;
use Illuminate\Support\Facades\Log;
use Illuminate\Cache\Repository as CacheRepository;

/**
 * This connects the Loyalty Platform to the Phi Mock CMS.
 */
class PhiMockConnector extends Connector
{
    /**
     * All of the player requests
     */
    use Traits\PlayerTrait;

    /**
     * All of the player requests
     */
    use Traits\PlayerEarningTrait;

    /**
     * For RealWin Earning Methods
     */
    use RealWinPlayerEarningTrait;

    /**
     * All of the property requests
     */
    use Traits\PropertyTrait;

    /**
     * Account redemption stuff
     */
    use Traits\PlayerRedemptionTrait;

    /**
     * Connection Settings.
     */
    use Traits\ConnectionSettingsTrait;

    /**
     * Supported features - what features are available via the Phi Mock connector
     */
    use Traits\SupportedFeaturesTrait;

    protected $apiKey;

    /**
     * Guzzle client
     */
    public $guzzle;

    /**
     * Cache store
     * @var CacheRepository
     */
    public $cache;

    /**
     *
     * @var int playerId
     */
    public $playerId;

    /**
     * Timeout in seconds for guzzle requests
     * @var integer
     */
    public $timeout = 5;  // 5 seconds

    /**
     * Construct the Phi Mock Connector
     *
     * @return void
     **/
    public function __construct()
    {
        // resolve the CacheRepository from Laravel
        $this->cache = resolve(CacheRepository::class);

        $account = Account::get()[0];
        config(['services.connector.account_id' => $account->id]);

        $connectorSettings = $account->connectorSettings;
        if (!$connectorSettings || !$connectorSettings->settings['mock_api_url']) {
            abort(response("Mock CMS Settings not found for account ID {$account->id}.", 500));
        }
        $settings = $connectorSettings->settings;

        /**
         * Define the connection credentials for Phi Mock CMS.
         */
        $this->baseUrl = $settings['mock_api_url'];
        $this->apiKey = $settings['mock_api_key'];

        /**
         * Caching for Mock CMS results
         */
        $this->cacheEnabled = (boolean)$account->cache_data_minutes;
        $this->cacheMinutes = $account->cache_data_minutes;
        $this->cachePlayerEnabled = (boolean)$account->cache_player_data_minutes;
        $this->cachePlayerMinutes = $account->cache_player_data_minutes;

        // Instantiate Guzzle for HTTP requests
        $this->initGuzzle();
    }

    /**
     * Returns the name of the Points account according to the
     * third party data
     * @return string
     */
    public function accountPointsName()
    {
        return 'Points';
    }

    /**
     * Returns the comps account name according to
     * the third party data
     * @return string
     */
    public function accountCompsName()
    {
        return 'Comps';
    }

    /**
     * Returns the promo account name
     * according to third party data.
     * @return [type] [description]
     */
    public function accountPromoName()
    {
        return 'Promo';
    }

    /**
     * Returns the promo account name
     * according to third party data.
     * @return [type] [description]
     */
    public function accountTheoName()
    {
        return 'Theo';
    }

    /**
     * Build the uri path for all non-authorization
     * calls to the origin api.
     *
     * @param string $path The path to append to the API URI
     * @return string
     **/
    public function uri($path = '')
    {
        return $this->basePath . '/api' . $path;
    }

    /**
     * Initialize the Guzzle object for requests
     *
     * @return void
     **/
    public function initGuzzle()
    {
        $this->guzzle = new Guzzle([
            'base_uri' => $this->baseUrl,
            'verify' => false,
            'timeout' => $this->timeout
        ]);
    }

    /**
     * Make an http request using guzzle.
     *
     * @param string $method HTTP request method
     * @param string $path   API path to request
     * @param array  $data   Data to pass to the request for POST, PUT requests
     * @return mixed|json
     **/
    public function request($method, $path, $data = [], $query = [])
    {
        $options = [
            'verify' => false,
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
                'Accept' => 'application/json'
            ]
        ];

        // Add JSON payload to setup if it
        if (!empty($data)) {
            $options['json'] = $data;
        }

        // Add query payload to setup if it
        if (!empty($query)) {
            $options['query'] = $query;
        }

        $requestString = '[' . $method . '] ' . $this->uri($path);

        // Time the request
        start_measure('request', 'Mock CMS Request : ' . $requestString . ' - ' . date('Y-m-d H:i:s'));
        Log::info("CURL request start: $requestString");
        $start = microtime(true);
        try {
            $response = $this->guzzle->request($method, $this->uri($path), $options);
        } catch (\Exception $e) {
            Log::info("Error in CURL request", ["code" => $e->getCode(), "message" => $e->getMessage()]);
            throw $e;
        }
        $duration = microtime(true) - $start;
        Log::info("CURL request end: $requestString - Ran for " . number_format($duration / 1000, 4) . " seconds.");
        // calculate duration
        stop_measure('request');


        // Return the response to the caller as determined by the
        // content-type response header.
        $contentType = $response->getHeader('Content-Type')[0];
        if (stripos($contentType, 'application/json') !== false) {
            // Return decoded JSON data.
            return json_decode($response->getBody());
        } else {
            // Return the raw contents.
            return (string)$response->getBody();
        }
    }

    /**
     * Make a GET request using guzzle.
     *
     * @param string $path The path to make the get request
     * @return mixed|json
     **/
    public function get($path, $query = [])
    {
        return $this->request('GET', $path, [], $query);
    }

    /**
     * Make POST request using guzzle.
     *
     * @param string $path The path for the POST request
     * @param array  $data The data to pass in the POST request
     * @return mixed|json
     **/
    public function post($path, $data)
    {
        return $this->request('POST', $path, $data);
    }

    /**
     * Make PUT request using guzzle.
     *
     * @param string $path The path for the POST request
     * @param array  $data The data to pass in the POST request
     * @return mixed|json
     **/
    public function put($path, $data)
    {
        return $this->request('PUT', $path, $data);
    }

    /**
     * Make DELETE request using guzzle.
     *
     * @param string $path The path for the POST request
     * @param array  $data The data to pass in the POST request
     * @return mixed|json
     **/
    public function delete($path, $data)
    {
        return $this->request('DELETE', $path, $data);
    }

    /**
     * Returns an array of buckets available via the Mock CMS connector.
     * @param bool $name
     * @return array|mixed|null
     */
    public function balanceDisplayOptions($name = false)
    {
        $options = [
            [
                'internal_identifier' => 'points',
                'identifier' => 'points',
                'show_on_kiosk' => true,
                'label' => 'Points',
                'currency' => false,
                'decimals' => 0,
                'sort' => 0,
            ],
            [
                'internal_identifier' => 'points-earned-today',
                'identifier' => 'points_earned_today',
                'show_on_kiosk' => true,
                'label' => 'Points Earned Today',
                'currency' => false,
                'decimals' => 0,
                'sort' => 1,
            ],
            [
                'internal_identifier' => 'comps',
                'identifier' => 'comps',
                'show_on_kiosk' => true,
                'label' => 'Comps',
                'currency' => true,
                'decimals' => 2,
                'sort' => 2,
            ],
            [
                'internal_identifier' => 'comps-earned-today',
                'identifier' => 'comps_earned_today',
                'show_on_kiosk' => true,
                'label' => 'Comps Earned Today',
                'currency' => true,
                'decimals' => 2,
                'sort' => 3,
            ],
            [
                'internal_identifier' => 'promo',
                'identifier' => 'promo',
                'show_on_kiosk' => true,
                'label' => 'Promo',
                'currency' => true,
                'decimals' => 2,
                'sort' => 4,
            ],
            [
                'internal_identifier' => 'promo-earned-today',
                'identifier' => 'promo_earned_today',
                'show_on_kiosk' => true,
                'label' => 'Promo Earned Today',
                'currency' => true,
                'decimals' => 2,
                'sort' => 4,
            ],
        ];

        if (!$name) {
            return $options;
        }

        return collect($options)->keyBy('identifier')->get($name, null);
    }

    /**
     * String based cache ID for referencing
     * and storing cached request data
     * @param  string $append Cache data identifier
     * @return string
     */
    public function cacheId($append)
    {
        return 'origin:phi-mock:' . config('services.connector.property_id') . ':' . $append;
    }

    /**
     * Returns a string indicating the version of the CMS API
     * the connector is currently working with if it can be
     * retrieved. Otherwise NULL is returned.
     *
     * @return string
     */
    public function apiVersion()
    {
        $cacheKey = 'origin.api_version';
        return $this->cache->remember($cacheKey, config('origin.cache.cms_api_connection_info_lifetime'), function() {
            try {

                $response = $this->get('/api_version');

                return $response->version;
            } catch (\Exception $e) {
                return null;
            }
        });
    }

}
