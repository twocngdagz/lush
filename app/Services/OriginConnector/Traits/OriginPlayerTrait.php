<?php

/**
 * This trait holds common player requests that do not interface
 * with the third party. The results and any input have already
 * been transformed by the Provider Transformer. For methods
 * that communicate directly with the third party provider
 * you should look in that provider's traits.
 */
namespace App\Services\OriginConnector\Traits;

use App\Models\Player;

use Carbon\Carbon;
use App\Services\OriginConnector\ConnectionException;
use App\Services\OriginConnector\Exceptions\PlayerAccountNotFoundException;
use Illuminate\Support\Facades\Log;

trait OriginPlayerTrait
{
    /**
     * Validate a player exists in the local database as well as in the CMS
     * @return Player|bool
     */
    public static function validatePlayer($playerId): Player|bool
    {
        $player = Player::find($playerId);
        if (!$player) {
            Log::error("ValidatePlayerTrait::validatePlayer - Player not found in local database - ID: $playerId");
            return false;
        }

        // Verify the player exists in the CMS before continuing
        $removePlayer = false;
        try {
            $extPlayer = Origin::getPlayer($player->ext_id);
        } catch (\Exception $e) {
            $removePlayer = true;
            Log::error("ValidatePlayerTrait::validatePlayer - Player not found in CMS - ExtID: {$player->ext_id}");
        }

        if ($removePlayer || !$extPlayer) {
            try {
                // Try removing the invalid player from the DB.
                // Removing this line causing the player delete when login using swipe
                $player->delete();
            } catch (\Exception $ex) {
                // do nothing for now.
                Log::error("Could not delete invalid player in ValidatePlayerTrait::validatePlayer - ID: $playerId");
            }
            return false;
        }

        return $player;
    }

    /**
     * Validate & update a player's PIN in a single action.
     * Connectors with different flows should override this method.
     *
     * @param string $extPlayerId ID of the Player to reset their PIN
     * @param string $pin Current PIN to verify
     * @param string $newPin New PIN to set
     * @param string $confirmPin Confirm new PIN
     * @param int $extPropertyId
     * @return bool
     */
    public function validateAndUpdatePlayerPin(string $extPlayerId, string $pin, string $newPin, string $confirmPin, int $extPropertyId): bool
    {
        $this->validatePinNumber($extPlayerId, $pin);
        return $this->updatePlayerPin($extPlayerId, $newPin, $confirmPin, $extPropertyId);
    }

    /**
     * Get a player rank ID from the Player ID provided
     *
     * @param int|string $id The player ID to retrieve.
     * @return int
     **/
    public function getPlayerRank(int $id): int
    {
        return $this->getPlayer($id)->rank;
    }

    /**
     * Get a player rank ID from the Player ID provided
     *
     * @param int $id The player ID to retrieve.
     * @return int
     **/
    public function getPlayerRankId(int $id): int
    {
        return $this->getPlayer($id)->rank->id;
    }

    /**
     * Get a player account by its name
     * @param int|string $playerId External player id
     * @param string $accountName Account name
     * @return object
     * @throws PlayerAccountNotFoundException
     */
    public function getPlayerAccountByName(int|string $playerId, string $accountName): object
    {
        return $this->rememberPlayer("getPlayerAccountByName:$playerId:$accountName", function () use ($playerId, $accountName) {
            $accounts = collect($this->getPlayerAccounts($playerId));
            $account = $accounts->where('name', $accountName)->first();

            if (!$account) {
                throw new PlayerAccountNotFoundException;
            }

            return $account;
        });
    }

    /**
     * Get a player account by its name
     * @param int $id External player id
     * @param int $accountTypeId
     * @return array
     * @throws PlayerAccountNotFoundException
     */
    public function getPlayerAccountType(int $id, int $accountTypeId): array
    {
        $account = collect($this->getPlayerAccounts($id))->where('id', $accountTypeId)->first();

        if (!$account) {
            throw new PlayerAccountNotFoundException;
        }

        return $account;
    }

    /**
     * Get the account balance for a player
     * @param int|string $id      Player ID
     * @param string $account Account Name
     * @return string          Formatted account balance
     */
    public function getPlayerAccountBalance(int|string $id, string $account): string
    {
        $val = $this->getPlayerAccountByName($id, $account);
        $bal = $val->amount;
        return number_format(floor($bal), 0);
    }


    /**
     * Get the points balance for this player
     * Because nConnect is... strange... we can get a 404 response
     * that would represent a balance of 0. Why they don't just
     * return a balance of zero is beyond me.
     *
     * @param int $id The player ID to retrieve.
     * @return float Formatted player balance
     **/
    public function getPlayerPointsBalance(int $id): float
    {
        return $this->getPlayerAccountBalance($id, $this->accountPointsName());
    }

    /**
     * Get the points balance for this player ID.
     * Because nConnect, we can get a 404 response
     * that would represent a balance of 0.
     *
     * @param int $id The player ID to retrieve.
     * @return string Formatted player balance
     **/
    public function getPlayerPromoBalance(int $id): float
    {
        return $this->getPlayerAccountBalance($id, $this->accountPromoName());
    }

    /**
     * Get the points balance for this player ID.
     * Because nConnect, we can get a 404 response
     * that would represent a balance of 0.
     *
     * @param int|string $id The player ID to retrieve.
     * @return string Formatted player balance
     **/
    public function getPlayerCompsBalance(int|string $id): string
    {
        return $this->getPlayerAccountBalance($id, $this->accountCompsName());
    }

    /**
     * Add to the player's comps balance
     *
     * @param int|string $playerId Player ID to influence the balance of
     * @param int $amount Amount to influence
     * @param $description
     * @param null $comment
     * @param Carbon $expiresAt
     * @return bool
     */
    public function addPlayerCompsBalance(int|string $playerId, int $amount, $description, $comment = null, Carbon $expiresAt = null): bool
    {
        return $this->addPlayerAccountBalance($playerId, $this->accountCompsName(), $amount, $description, $comment, $expiresAt);
    }

    /**
     * Add to the player's promo balance
     *
     * @param int $id Player ID to influence the balance of
     * @param int $amount Amount to influence
     * @param string $description
     * @param string|null $comment
     * @param Carbon $expiresAt
     * @return bool
     */
    public function addPlayerPromoBalance(int $id, int $amount, string $description, ?string $comment = null, ?Carbon $expiresAt = null): bool
    {
        return $this->addPlayerAccountBalance($id, $this->accountPromoName(), $amount, $description, $comment, $expiresAt);
    }

    /**
     * Add to the player's points balance
     *
     * @param int $id Player ID to influence the balance of
     * @param int $amount Amount to influence
     * @param string $description
     * @param string|null $comment
     * @param Carbon $expiresAt
     * @return bool
     */
    public function addPlayerPointsBalance(int $id, int $amount, string $description, ?string $comment = null, ?Carbon $expiresAt = null): bool
    {
        return $this->addPlayerAccountBalance($id, $this->accountPointsName(), $amount, $description, $comment, $expiresAt);
    }

    /**
     * Add to the player's tier points balance
     *
     * @param int|string $playerId Player ID to influence the balance of
     * @param int $amount Amount to influence
     * @param $description
     * @param null $comment
     * @param Carbon $expiresAt
     * @return bool
     */
    public function addPlayerTierPointsBalance(int|string $playerId, int $amount, $description, $comment = null, Carbon $expiresAt = null): bool
    {
        return $this->addPlayerAccountBalance($playerId, $this->accountTierPointsName(), $amount, $description, $comment, $expiresAt);
    }

    /**
     * Convert promo balance to dollars
     *
     * @param mixed $rankId The player's rank/tier ID
     * @param float|int $balance The account balance from the oasis API for Account type "promo"
     * @param string $prefix Optional : Prefix to prepend to the returned number
     * @return string
     **/
    public function convertPointsBalanceToDollars(mixed $rankId, float|int $balance, string $prefix = ''): string
    {
        $balance = (int)str_replace(',', '', $balance);

        return $prefix . number_format($balance / $this->getPropertyPointsPerDollar(), 2);
    }
}
