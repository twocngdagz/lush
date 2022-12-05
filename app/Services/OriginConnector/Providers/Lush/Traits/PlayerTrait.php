<?php

namespace App\Services\OriginConnector\Providers\Lush\Traits;

use App\Models\Kiosk\CardPrintDetail;
use App\Models\Player;
use App\Services\OriginConnector\ConnectionException;
use App\Services\OriginConnector\Exceptions\InvalidPinException;
use App\Services\OriginConnector\Exceptions\PlayerUpdateProfileException;
use App\Services\OriginConnector\Providers\Lush\Models\LushAccount;
use App\Services\OriginConnector\Providers\Lush\Models\LushPlayer;
use App\Services\OriginConnector\Providers\Lush\Models\LushRank;
use App\Services\OriginConnector\Providers\Lush\Transformers\LushPlayerTransformer;
use App\Services\OriginConnector\Providers\PhiMock\Transformers\PlayerAccountTransformer;
use App\Services\OriginConnector\Providers\PhiMock\Transformers\PlayerGroupTransformer;
use Carbon\Carbon;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Propaganistas\LaravelPhone\PhoneNumber;

trait PlayerTrait
{
    public function getPlayer(int $extPlayerId): array
    {
        Log::info("ORIGIN : getPlayer - Ext ID:{$extPlayerId}");

        // Return results cached for the current request if available.
        $cacheId = "getLushPlayer:playerExtId={$extPlayerId}";

        $result = $this->rememberPlayer($cacheId, function () use ($extPlayerId, $cacheId) {
            Log::info("Collecting and caching player: $cacheId");
            try {
                \Log::info("Finding player by player ID $extPlayerId");
                $player = LushPlayer::where('id', $extPlayerId)->first();
                if (!$player) {
                    \Log::info("Finding player by swipe ID");
                    $player = LushPlayer::where('card_swipe_data', $extPlayerId)->firstOrFail();
                }
            } catch (\Exception $e) {
                Log::error(['Error calling getPlayer', $e->getFile(), $e->getLine(), $e->getMessage()]);
            }
            Log::info("Finished collecting and caching player: $cacheId");

            return transformify((object)$player, new LushPlayerTransformer);
        });
        Log::info("ORIGIN : getPlayer - Ext ID:{$extPlayerId} Complete");

        return $result;
    }

    public function getPlayerAccounts(int $extPlayerId): array
    {
        Log::info("ORIGIN : getPlayerAccounts - Ext ID:{$extPlayerId}");

        // Return results cached for the current request if available.
        $cacheId = "getLushPlayerAccounts:playerExtId={$extPlayerId}";

        $result = [];

        try {
            $result = $this->rememberPlayer($cacheId, function () use ($extPlayerId, $cacheId) {
                Log::info("Collecting and caching player accounts: $cacheId");
                $player = LushPlayer::where('id', $extPlayerId)->first();
                $result = transformify($player->lushaccounts, new PlayerAccountTransformer);
                Log::info("Finished collecting and caching player accounts: $cacheId");

                return $result;
            });
        }  catch (\Exception $e) {
            Log::error(['Error calling getPlayer', $e->getFile(), $e->getLine(), $e->getMessage()]);
        }
        Log::info("ORIGIN : getPlayerAccounts - Ext ID:{$extPlayerId} complete");

        return collect($result);
    }

    public function getPlayerFromSwipeId(string $swipeId): array
    {
        $trackData = explode(';', $swipeId);
        $swipeId = trim(preg_replace('/\D/', '', $trackData[0]));

        Log::info("ORIGIN : getPlayerFromSwipeId - Swipe ID:{$swipeId}");

        $result = $this->rememberPlayer("getLushPlayerFromSwipeId:$swipeId", function () use ($swipeId) {
            try {
                $player = LushPlayer::where('card_swipe_data', $swipeId)->firstOrFail();
                $player->load('lushrank');

                return transformify($player, new LushPlayerTransformer);
            } catch (\Exception $e) {
                Log::error(['Error in getPlayerFromSwipeId', $e->getFile(), $e->getLine(), $e->getMessage()]);
            }
        });
        Log::info("ORIGIN : getPlayerFromSwipeId - Swipe ID:{$swipeId} complete");

        return $result;
    }

    public function validatePinNumber(int $extPlayerId, string $pin): bool
    {
        Log::info("ORIGIN : validatePinNumber - Ext ID:{$extPlayerId}");

        $data = [
            'pin' => $pin
        ];


            Validator::make($data, [
                'pin' => 'required|numeric|digits:4'
            ])->validated();

            $player = LushPlayer::findOrFail($extPlayerId);


            if ($player->card_pin_attempts >= 3) {
                \Log::warning("Attempt to validate deactivated PIN by player {$player->id}");
                throw new InvalidPinException('Your PIN maybe be deactivated due to multiple incorrect attempts.');
            }

            if ($player->card_pin == $pin) {
                \Log::info("Successfully validated PIN for player {$player->id}");
                $player->update(['card_pin_attempts' => 0]);

                return true;
            }

            \Log::warning("Invalid PIN entered for player {$player->id}");
            $player->increment('card_pin_attempts');

            throw new InvalidPinException;

    }

    public function addPlayerAccountBalance(
        int $extPlayerId,
        int $accountName,
        int $amount,
        string $description = 'Loyalty Rewards Deposit',
        ?string $comment = 'Loyalty Rewards Deposit',
        ?Carbon $expiresAt = null
    ): string {
        // Get the account type for the accountName
        $playerAccount = $this->getPlayerAccounts($extPlayerId)->firstWhere('name', $accountName);
        $account = LushAccount::findOrFail($playerAccount->id);

        // Build up a unique ID for the ExternalTransaction.
        $transactionId = $extPlayerId . now()->format('YmdHisuO');

        $data = [
            'amount' => $amount,
        ];



        try {
            Validator::make($data, [
                'amount' => 'required'
            ])->validate();
            $amountToIncrease = ($account->is_currency) ? $amount * 100 : $amount;

            $account->increment('balance', $amountToIncrease);

            return $transactionId;
        } catch (\Exception $e) {
            Log::error(['Error in getPlayerFromSwipeId', $e->getFile(), $e->getLine(), $e->getMessage()]);
            throw new ConnectionException('There was an error in your account transaction.', $e->getCode());
        }
    }

        public function getPlayerGroups(int $extPlayerId): array
    {
        return $this->rememberPlayer('getLushPlayerGroups:' . $extPlayerId, function () use ($extPlayerId) {
            try {
                $player = LushPlayer::findOrFail($extPlayerId);
                return transformify(collect($player->lushgroups), new PlayerGroupTransformer);
            } catch (\Exception $e) {
                Log::error(['Error in getPlayerGroups', $e->getFile(), $e->getLine(), $e->getMessage()]);
                throw new ConnectionException('There was an error accessing your player groups.', $e->getCode());
            }
        });
    }

    public function updatePlayerProfile(Player $player, array $params = []): bool
    {
        try {
            $data = [
                'email' => $params['email'],
                'phone' => preg_replace("/[^0-9]/", '', PhoneNumber::make($params['phoneNumber'], 'US')->lenient()->formatNational()),
                'email_opt_in' => $params['emailOptIn'],
                'phone_opt_in' => $params['phoneOptIn'],
            ];

            $validatedData = Validator::make($data, [
                'pin' => 'nullable|numeric|digits:4',
                'phone' => 'nullable',
                'email' => 'nullable|email',
                'email_opt_in' => 'boolean',
                'phone_opt_in' => 'boolean',
            ])->validate();

            $lushPlayer = LushPlayer::findOrFail($player->ext_id);

            $lushPlayer->update($validatedData);

            return true;
        } catch (\Exception $e) {
            Log::error(['Error in calling updatePlayerProfile', $e->getFile(), $e->getLine(), $e->getMessage()]);
            throw new PlayerUpdateProfileException($e->getMessage());
        }
    }

    public function enrollPlayer(\StdClass $playerData, object $kiosk)
    {
        Log::info("ORIGIN : enrollPlayer - Name: {$playerData->first_name} {$playerData->last_name}");

        $data = [
            'id_type' => $playerData->id_type,
            'id_number' => $playerData->id_number,
            'id_expiration_date' => date('Y-m-d', strtotime($playerData->expiration_date)),
            'first_name' => $playerData->first_name,
            'middle_initial' => $playerData->middle_initial ?? null,
            'last_name' => $playerData->last_name,
            'birthday' => date('Y-m-d', strtotime($playerData->date_of_birth)),
            'email' => $playerData->email ?? null,
            'gender' => $playerData->gender ?? null,
            'lush_rank_id' => LushRank::orderBy('threshold')->first()->id,
            'address' => $playerData->address_1 ?? null,
            'address_2' => $playerData->address_2 ?? null,
            'city' => $playerData->city ?? null,
            'state' => $playerData->state,
            'zip' => $playerData->postal_code ?? null,
            'country' => $playerData->country,
            'email_opt_in' => $playerData->email_opt_in ?? true,
            'phone_opt_in' => $playerData->phone_opt_in ?? true,
        ];
        if (!empty($playerData->phone)) {
            $data['phone'] = preg_replace(
                "/[^0-9]/",
                '',
                PhoneNumber::make($playerData->phone, 'US')->lenient()->formatNational()
            );
        }
        try {

            $validatedData = Validator::make($data, [
                'id_type' => 'required',
                'id_number' => 'required',
                'id_expiration_date' => 'required|date-format:Y-m-d',
                'first_name' => 'required',
                'middle_initial' => 'nullable',
                'last_name' => 'required',
                'birthday' => 'required|date-format:Y-m-d',
                'phone' => 'nullable|string',
                'email' => 'nullable|email',
                'gender' => 'nullable|in:M,F,U',
                'lush_rank_id' => 'nullable',
                'address' => 'nullable|string',
                'address_2' => 'nullable|string',
                 'city' => 'nullable|string',
                'state' => 'nullable|string',
                'zip' => 'nullable|us_postal_code',
                'country' => 'nullable|string',
                'email_opt_in' => 'boolean',
                'sms_opt_in' => 'boolean',
            ])->validate();

            $player = LushPlayer::create($validatedData);
            Log::info("Result of post to Mock /players");
            Log::info(json_encode($player));
            Log::info("Player ID: " . $player->id);

            $player = $this->getPlayer($player->id);
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
                'payload' => json_encode($data),
            ]);
            throw new \Exception('There was an error enrolling the player.');
        }
        Log::info("ORIGIN : enrollPlayer - ID: {$player->id} Ext ID: {$player->ext_id} Name: {$player->full_name} complete");

        return $player;
    }

    public function getPlayerDetail(Player $player, $with_accounts = true)
    {
        Log::info("ORIGIN : getPlayerDetail - ID:{$player->id} Ext ID:{$player->ext_id}");

        // Return results cached for the current request if available.
        $cacheId = "getPlayerDetail:lush_player=" . $player->id . ($with_accounts ? ":with_accounts" : "");

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

    public function getPlayerFromScan(object $scannerResponse) {

        $data = [
            'first_name' => $scannerResponse->firstName,
            'last_name' => $scannerResponse->lastName,
        ];

        $validatedData = Validator::make($data, [
            'first_name' => 'required',
            'last_name' => 'required',
        ])->validated();

        \Log::info("Finding player by player name -> " . $validatedData['first_name']. $validatedData['last_name']);

        $player = Player::where('first_name', $validatedData['first_name'])
            ->where('last_name', $validatedData['last_name'])
            ->first();


        if (!$player) {
            return false;
        }
    }

    public function updatePlayerPin($playerId, $pin)
    {
        $payload = [
            'card_pin' => trim($pin),
        ];

        try {
            $validatedData = Validator::make($payload, [
                'card_pin' => 'nullable|numeric|digits:4',
                'phone' => 'nullable',
                'email' => 'nullable|email'
            ])->validate();
            $lushPlayer = LushPlayer::findOrFail($playerId);
            $lushPlayer->update($validatedData);

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


}
