<?php

/**
 * This API trait contains functionality for player related info supplied and
 * consumed by the Phi Mock API.
 */

namespace App\Services\OriginConnector\Providers\PhiMock\Traits;

use App\Models\Kiosk;
use App\Models\CardPrintDetail;
use App\Models\Player;
use App\Services\OriginConnector\ConnectionException;
use App\Services\OriginConnector\Exceptions\InvalidPinException;
use App\Services\OriginConnector\Exceptions\PlayerExcludedException;
use App\Services\OriginConnector\Exceptions\PlayerNotFoundException;
use App\Services\OriginConnector\Exceptions\InvalidPlayerIdException;
use App\Services\OriginConnector\Exceptions\PlayerUpdatePinException;
use App\Services\OriginConnector\Exceptions\UnknownCardNumberException;
use App\Services\OriginConnector\Exceptions\PlayerUpdateProfileException;
use App\Services\OriginConnector\Exceptions\UnauthorizedRequestException;
use App\Services\OriginConnector\Exceptions\ConnectionUnavailableException;
use App\Services\OriginConnector\Exceptions\PlayerAccountNotFoundException;
use App\Services\OriginConnector\Providers\PhiMock\Transformers\PlayerTransformer;
use App\Services\OriginConnector\Providers\PhiMock\Transformers\PlayerGroupTransformer;
use App\Services\OriginConnector\Providers\PhiMock\Transformers\PlayerAccountTransformer;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;
use GuzzleHttp\Exception\ClientException;
use Propaganistas\LaravelPhone\PhoneNumber;

trait PlayerTrait
{
    /**
     * Validate a PIN code for a player.
     * Pass the PlayerId and the players PIN number
     * The PIN number will be encoded on this end.
     *
     * @param  int $extPlayerId The player ID
     * @param  int $pin         The 4 digit PIN number to validate
     * @return bool
     **/
    public function validatePinNumber($extPlayerId, $pin)
    {
        Log::info("ORIGIN : validatePinNumber - Ext ID:{$extPlayerId}");

        $data = [
            'pin' => $pin
        ];

        try {
            $this->post("/players/$extPlayerId/validate-pin", $data);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            $code = $e->getResponse()->getStatusCode();
            Log::error(['Error in validatePinNumber', $e->getFile(), $e->getLine(), $e->getMessage()]);
            switch ($code) {
                case 400:
                    // throw new BlockedPinException;
                    throw new InvalidPinException('Your PIN maybe be deactivated due to multiple incorrect attempts.');
                    break;
                case 401:
                    throw new InvalidPinException;
                    break;
                default:
                    throw new ConnectionException($e->getMessage(), $e->getCode());
                    break;
            }
        } catch (\Exception $e) {
            Log::error(['Error in validatePinNumber', $e->getFile(), $e->getLine(), $e->getMessage()]);
            throw new ConnectionUnavailableException;
        }
        Log::info("ORIGIN : validatePinNumber - Ext ID:{$extPlayerId} complete");

        // If the request does not throw an exception then we'll return true
        return true;
    }

    /**
     * Get a player from a card swipe ID
     *
     * @param string $swipeId Card identifier retrieved from a successful swipe
     * @return object
     **/
    public function getPlayerFromSwipeId($swipeId)
    {
        $trackData = explode(';', $swipeId);
        $swipeId = trim(preg_replace('/\D/', '', $trackData[0]));

        Log::info("ORIGIN : getPlayerFromSwipeId - Swipe ID:{$swipeId}");

        $result = $this->rememberPlayer("getPlayerFromSwipeId:$swipeId", function () use ($swipeId) {
            try {
                $response = $this->get("/players/$swipeId");

                return transformify((object)$response, new PlayerTransformer);
            } catch (\Exception $e) {
                Log::error(['Error in getPlayerFromSwipeId', $e->getFile(), $e->getLine(), $e->getMessage()]);
                switch ($e->getCode()) {
                    case 404:
                        throw new UnknownCardNumberException;
                        break;
                    case 400:
                        throw new InvalidPlayerIdException;
                        break;
                    case 418:
                        throw new PlayerExcludedException($swipeId);
                        break;
                    default:
                        throw new ConnectionUnavailableException;
                        break;
                }
            }
        });
        Log::info("ORIGIN : getPlayerFromSwipeId - Swipe ID:{$swipeId} complete");

        return $result;
    }

    /**
     * Enroll player from kiosk
     *
     * @param \StdClass $playerData
     * @return boolean
     * @throws ConnectionUnavailableException
     */
    public function enrollPlayer(\StdClass $playerData, object $kiosk)
    {
        Log::info("ORIGIN : enrollPlayer - Name: {$playerData->first_name} {$playerData->last_name}");

        $transactionPayload = [
            'id_type' => $playerData->id_type,
            'id_number' => $playerData->id_number,
            'id_expiration_date' => date('Y-m-d', strtotime($playerData->expiration_date)),
            'first_name' => $playerData->first_name,
            'middle_initial' => $playerData->middle_initial ?? null,
            'last_name' => $playerData->last_name,
            'birthday' => date('Y-m-d', strtotime($playerData->date_of_birth)),
            'email' => $playerData->email ?? null,
            'gender' => $playerData->gender ?? null,
            'address' => $playerData->address_1 ?? null,
            'address_2' => $playerData->address_2 ?? null,
            'city' => $playerData->city ?? null,
            'state' => $playerData->state,
            'zip' => $playerData->postal_code ?? null,
            'country' => $playerData->country,
        ];
        if (!empty($playerData->phone)) {
            $transactionPayload['phone'] = preg_replace(
                "/[^0-9]/",
                '',
                PhoneNumber::make($playerData->phone, 'US')->lenient()->formatNational()
            );
        }
        try {
            $result = $this->post("/players", $transactionPayload);
            Log::info("Result of post to Mock /players");
            Log::info(json_encode($result));
            Log::info("Player ID: " . $result->id);

            $player = $this->getPlayer($result->id);
            Log::info("Player found? " . json_encode($player));

            $cardData = [
                'player_id' => $player->id,
                'first_name' => $player->profile->first_name,
                'last_name' => $player->profile->last_name,
                'track_1' => $player->id,
                'track_2' => $player->id,
                'tier' => $player->rank->id,
                'card_id' => null, // this is null - this only used for SYNKROS
                'action_type' => 'enrollment',
                'kiosk_id' => $kiosk->id
            ];

            $cardDetails = CardPrintDetail::create($cardData);
            Log::info("CardPrintDetail created: " . json_encode($cardDetails));

            return $player;
        } catch (\Exception $e) {
            Log::error([
                'error' => 'Error in enrollPlayer.',
                'message' => ($e instanceof ClientException)
                    ? $e->getResponse()->getBody()->getContents()
                    : $e->getMessage(),
                'file' => "{$e->getFile()}:{$e->getLine()}",
                'payload' => json_encode($transactionPayload),
            ]);
            switch ($e->getCode()) {
                case 422:
                    throw new \Exception('There was an error enrolling the player.');
                    break;
                default:
                    throw new ConnectionUnavailableException;
                    break;
            }
        }
        Log::info("ORIGIN : enrollPlayer - ID: {$player->id} Ext ID: {$player->ext_id} Name: {$player->full_name} complete");

        return $player;
    }

    /**
     * Get player from ID
     *
     * @param int $extPlayerId The player ID to retrieve.
     * @return object
     **/
    public function getPlayer($extPlayerId)
    {
        Log::info("ORIGIN : getPlayer - Ext ID:{$extPlayerId}");

        // Return results cached for the current request if available.
        $cacheId = "getPlayer:playerExtId={$extPlayerId}";

        $result = $this->rememberPlayer($cacheId, function () use ($extPlayerId, $cacheId) {
            Log::info("Collecting and caching player: $cacheId");
            try {
                $player = $this->get("/players/$extPlayerId");
            } catch (\Exception $e) {
                Log::error(['Error calling getPlayer', $e->getFile(), $e->getLine(), $e->getMessage()]);
                switch ($e->getCode()) {
                    case 404:
                        throw new PlayerNotFoundException;
                        break;
                    case 400:
                        throw new InvalidPlayerIdException;
                        break;
                    case 418:
                        throw new PlayerExcludedException($extPlayerId);
                        break;
                    default:
                        throw new ConnectionUnavailableException;
                        break;
                }
            }
            Log::info("Finished collecting and caching player: $cacheId");

            return transformify((object)$player, new PlayerTransformer);
        });
        Log::info("ORIGIN : getPlayer - Ext ID:{$extPlayerId} Complete");

        return $result;
    }

    /**
     * Get player detail from the CMS
     *
     * @param Player $player The player to retrieve details for.
     * @param bool   $with_accounts
     * @return object
     */
    public function getPlayerDetail(Player $player, $with_accounts = true)
    {
        Log::info("ORIGIN : getPlayerDetail - ID:{$player->id} Ext ID:{$player->ext_id}");

        // Return results cached for the current request if available.
        $cacheId = "getPlayerDetail:player=" . $player->id . ($with_accounts ? ":with_accounts" : "");

        $result = $this->rememberPlayer($cacheId, function () use ($player, $with_accounts, $cacheId) {
            Log::info("Collecting and caching player detail: $cacheId");

            // Get the player from the CMS.
            try {
                $externalPlayer = $this->getPlayer($player->ext_id);
            } catch (\Exception $e) {
                \Log::error(['Error calling getPlayerDetail', $e->getFile(), $e->getLine(), $e->getMessage()]);
                return false;
            }

            // Persist the player's name in our DB if needed.
            $player->update([
                'first_name' => $externalPlayer->profile->first_name,
                'last_name' => $externalPlayer->profile->last_name,
            ]);

            $ret = [
                'id' => $player->id,
                'ext_id' => $player->ext_id,
                'name' => $externalPlayer->profile->full_name,
                'first_name' => $externalPlayer->profile->first_name,
                'last_name' => $externalPlayer->profile->last_name,
                'rank' => preg_replace('/[0-9]+/', '', $externalPlayer->rank->name),
                'rank_id' => $externalPlayer->rank->id,
                'employee' => $externalPlayer->rank->name == 'Employee',
                'rank_name' => $externalPlayer->rank->name,
                'birth_date' => $externalPlayer->profile->birth_date ?? null,
                'age' => $externalPlayer->profile->age ?? null,
                'gender' => $externalPlayer->profile->gender ?? null,
                'registered_date' => $externalPlayer->profile->registered_date ?? null,
                'days_since_registration' => $externalPlayer->profile->days_since_registration ?? null,
                'email' => $externalPlayer->profile->email ?? null,
                'email_opt_in' => $externalPlayer->profile->email_opt_in ?? null,
                'phone' => $externalPlayer->profile->phone ?? null,
                'phone_opt_in' => $externalPlayer->profile->phone_opt_in ?? null,
                'address' => isset($externalPlayer->profile->address) ? (array)$externalPlayer->profile->address : null,
                'accounts' => [],
            ];

            if ($with_accounts) {
                try {
                    $accounts = $this->getPlayerAccounts($player->ext_id);
                } catch (\Exception $e) {
                    Log::error("Error collecting player accounts: {$e->getMessage()}");
                    Log::error("Trying to collect player accounts again.");
                    try {
                        sleep(1);
                        $accounts = $this->getPlayerAccounts($player->ext_id);
                    } catch (\Exception $e) {
                        Log::error("Error collecting player accounts: {$e->getMessage()}");
                    }
                }
                if (!empty($accounts)) {
                    foreach ($accounts as $account) {
                        $ret['accounts'][$account->internal_identifier] = (array)$account;
                    }
                }
            }
            Log::info("Finished collecting and caching player detail: $cacheId");

            return $ret;
        });
        Log::info("ORIGIN : getPlayerDetail - ID:{$player->id} Ext ID:{$player->ext_id} Complete");

        return $result;
    }

    /**
     * Get player rank
     * @param  integer $extPlayerId Player ID
     * @return object
     */
    public function getPlayerRank($extPlayerId)
    {
        return $this->getPlayer($extPlayerId)->rank;
    }

    /**
     * Get player info
     *
     * @param int $extPlayerId The player ID to retrieve.
     * @return Collection
     **/
    public function getPlayerAccounts($extPlayerId)
    {
        Log::info("ORIGIN : getPlayerAccounts - Ext ID:{$extPlayerId}");

        // Return results cached for the current request if available.
        $cacheId = "getPlayerAccounts:playerExtId={$extPlayerId}";

        $result = [];

        try {
            $result = $this->rememberPlayer($cacheId, function () use ($extPlayerId, $cacheId) {
                Log::info("Collecting and caching player accounts: $cacheId");
                $response = $this->get("/players/$extPlayerId/accounts");
                $result = transformify(collect($response->data), new PlayerAccountTransformer);
                Log::info("Finished collecting and caching player accounts: $cacheId");

                return $result;
            });
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            Log::error(['Error in getPlayerAccounts', $e->getFile(), $e->getLine(), $e->getMessage()]);
            switch ($e->getCode()) {
                case 404:
                    throw new PlayerAccountNotFoundException();
                    break;
                case 401:
                    throw new UnauthorizedRequestException($e->getMessage());
                    break;
                default:
                    throw new ConnectionException($e->getMessage(), $e->getCode());
                    break;
            }
        } catch (\Exception $e) {
            Log::info('ERROR - ' . $e->getMessage());
            throw new ConnectionException('There was an error accessing your accounts.', $e->getCode());
        }
        Log::info("ORIGIN : getPlayerAccounts - Ext ID:{$extPlayerId} complete");

        return collect($result);
    }

    /**
     * Add to the balance of a specific account
     *
     * @param        $extPlayerId
     * @param        $accountName
     * @param        $amount
     * @param string $description
     * @param string $comment
     * @param        $expiresAt
     * @return bool
     */
    public function addPlayerAccountBalance(
        $extPlayerId,
        $accountName,
        $amount,
        $description = 'Loyalty Rewards Deposit',
        $comment = 'Loyalty Rewards Deposit',
        Carbon $expiresAt = null
    ) {
        // Get the account type for the accountName
        $account = $this->getPlayerAccounts($extPlayerId)->firstWhere('name', $accountName);

        // Build up a unique ID for the ExternalTransaction.
        $rightNow = now();
        $transactionId = $extPlayerId . $rightNow->format('YmdHisuO');

        $transactionPayload = [
            'transaction_id' => $transactionId,
            'amount' => $amount,
            'comment' => str_replace("&", "and", $comment),
            'expires_at' => ($expiresAt) ? $expiresAt->format('Y-m-d\TH:i:s.uP') : null,
        ];

        if (isset($this->playerId)) {
            $extPlayerId = $this->playerId;
        }

        try {
            $this->post("/players/$extPlayerId/accounts/$account->id/deposit", $transactionPayload);

            return $transactionId;
        } catch (\Exception $e) {
            \Log::error([
                'error' => 'Error in account transaction.',
                'message' => ($e instanceof ClientException) ? $e->getResponse()->getBody()->getContents() : $e->getMessage(),
                'payload' => $transactionPayload,
            ]);
            throw new ConnectionException('There was an error in your account transaction.', $e->getCode());
        }
    }

    /**
     * Get all groups for this player ID.
     *
     * @param int $extPlayerId Player ID.
     * @return array
     **/
    public function getPlayerGroups($extPlayerId)
    {
        return $this->rememberPlayer('getPlayerGroups:' . $extPlayerId, function () use ($extPlayerId) {
            try {
                $result = $this->get("/players/$extPlayerId/groups");

                return transformify(collect($result->data), new PlayerGroupTransformer);
            } catch (\Exception $e) {
                Log::error(['Error in getPlayerGroups', $e->getFile(), $e->getLine(), $e->getMessage()]);
                switch ($e->getCode()) {
                    case 404:
                        return collect([]);
                        break;
                    default:
                        throw new ConnectionException('There was an error accessing your player groups.', $e->getCode());
                        break;
                }
            }
        });
    }

    /**
     * Update the player's PIN.
     *
     * @param int    $playerId Player ID.
     * @param string $pin      New player pin.
     * @return bool
     */
    public function updatePlayerPin($playerId, $pin)
    {
        $payload = [
            'pin' => trim($pin),
        ];

        try {
            // Update the PIN
            $this->put("/players/$playerId", $payload);

            return true;
        } catch (\Exception $e) {
            \Log::error([
                'error' => 'Error updating player PIN.',
                'message' => ($e instanceof ClientException) ? $e->getResponse()->getBody()->getContents() : $e->getMessage(),
                'payload' => $payload,
            ]);
            throw new PlayerUpdatePinException('Pin was not accepted. Please visit the Player\'s club for assistance.');
        }
    }

    /**
     * Update the player's profile info.
     *
     * @param string $extPlayerId   ID of the player to update their profile
     * @param string $extPropertyId ID of the Property to make the request to
     * @param array  $params        An array that contains the updated profile data
     * @return bool
     **/
    public function updatePlayerProfile(Player $player, array $params = []): bool
    {
        try {
            $payload = [
                'email' => $params['email'],
                'phone' => preg_replace("/[^0-9]/", '', PhoneNumber::make($params['phoneNumber'], 'US')->lenient()->formatNational()),
            ];

            $this->put("/players/$player->ext_id", $payload);

            return true;
        } catch (\Exception $e) {
            Log::error(['Error in calling updatePlayerProfile', $e->getFile(), $e->getLine(), $e->getMessage()]);
            switch ($e->getCode()) {
                case 404:
                    throw new PlayerNotFoundException;
                    break;
                case 400:
                    throw new InvalidPlayerIdException;
                    break;
                default:
                    throw new PlayerUpdateProfileException($e->getMessage());
                    break;
            }
        }
    }

    public function getPlayerCardPrintInfo(object $scannerResponse, object $kiosk, string $pin)
    {
        try {

            list($playerId, $player) = $this->getPlayerFromScan($scannerResponse);

            if($playerId && $player) {
                return [
                    'player_id' => $playerId,
                    'first_name' => $player->profile->first_name,
                    'last_name' => $player->profile->last_name,
                    'track_1' => $playerId,
                    'track_2' => $playerId,
                    'tier' => $player->rank->id,
                    'card_id' => null,
                    'action_type' => 'reprint',
                    'kiosk_id' => $kiosk->id
                ];
            }

            // Simulating Lost Card
            return [
                'player_id' => 142,
                'first_name' => 'SHAWN',
                'last_name' => 'SCRANTON',
                'track_1' => 142,
                'track_2' => 142,
                'tier' => 1,
                'card_id' => null,
                'action_type' => 'reprint',
                'kiosk_id' => $kiosk->id
            ];


        } catch (\Exception $e) {
            Log::error(["Error calling Origin::getPlayerCardPrintInfo()", $e->getFile(), $e->getLine(), $e->getMessage()]);
            throw $e;
        }
    }

    public function getPlayerFromScan(object $scannerResponse) {
        $player = $this->post('/players/search', [
            'first_name' => $scannerResponse->firstName,
            'last_name' => $scannerResponse->lastName,
            // 'birthday' => $scannerResponse->birthDate,
            // 'driversLicense' => $scannerResponse->documentType == 'DL' ? $scannerResponse->documentNumber : null
        ]);

        if(isset($player->code) && $player->code === 404) {
            return false; // Player Not Found
        }

        // For simulating lost card with existing player id 142
        //$player = $this->get("/players/142");

        return $player ? [$player->id, $this->getPlayer($player->id)] : false;
    }

    /**
     * Returns a collection containing player activity data.
     *
     * @param [type] $extPlayerId
     * @param integer $days
     * @return void
     */
    public function getPlayerGamingActivityReportGraph($extPlayerId, $days = 7)
    {
    }
}
