<?php

namespace App\Services\OriginConnector\Contracts;

use App\Models\Player;
use Carbon\Carbon;

/**
 * Player interface for all system required player calls.
 */
interface PlayerContract {
    /**
     * get a single player from swipe input
     * @param string $swipeId Swipe identifier read at the kiosk
     * @return array
     */
    public function getPlayerFromSwipeId($swipeId): array;

    /**
     * Validate a player's pin number
     *
     * @param integer $playerId Player Id
     * @param string $pin Pin number
     * @return bool
     */
    public function validatePinNumber($playerId, $pin): bool;

    /**
     * Get a single player from the external ID
     * @param  integer $id External ID for the player
     * @return array
     */
    public function getPlayer($id): array;

    /**
     * Get the identifier for a single rank
     * @param  integer $id External ID for the player
     * @return integer
     */
    public function getPlayerRankId($id): int;

    /**
     * Get player accounts
     * @param  integer $id External ID for the player
     * @return array
     */
    public function getPlayerAccounts($id): array;

    /**
     * Get player account type
     * @param  integer $id External ID for the player
     * @param  integer $accountTypeId External ID for the type of account to access
     * @return array
     */
    public function getPlayerAccountType($id, $accountTypeId): array;

    /**
     * Get player points balance
     * @param  integer $id External ID for the player
     * @return float
     */
    public function getPlayerPointsBalance($id): float;

    /**
     * Get player promo balance
     * @param  integer $id External ID for the player
     * @return float
     */
    public function getPlayerPromoBalance($id): float;

    /**
     * Add points to a player's balance
     * @param  integer $id External ID for the player
     * @param  integer $amount The amount to add
     * @param  string $description Description of the transaction
     * @param  string $comment An optional additional comment for the transaction
     * @param  Carbon $expiresAt When the awarded value expires
     * @return void
     */
    public function addPlayerPointsBalance($id, $amount, $description, $comment = null, Carbon $expiresAt = null): void;

    /**
     * Add promo to a player's balance
     * @param  integer $id External ID for the player
     * @param  integer $amount The amount to add
     * @param  string $description Description of the transaction
     * @param  string $comment An optional additional comment for the transaction
     * @param  Carbon $expiresAt When the awarded value expires
     * @return void
     */
    public function addPlayerPromoBalance($id, $amount, $description, $comment = null, Carbon $expiresAt = null): void;

    /**
     * Add value to a player's account
     * @param  integer $id External ID for the player
     * @param  integer $accountId Account ID for the player
     * @param  integer $amount The amount to add
     * @param  string $description Description of the transaction
     * @param  string $comment An optional additional comment for the transaction
     * @param  Carbon $expiresAt When the awarded value expires
     * @return void
     */
    public function addPlayerAccountBalance($id, $accountId, $amount, $description, $comment = null, Carbon $expiresAt = null): void;

    /**
     * Get a player's groups
     * @param  integer $id External ID for the player
     * @return array
     */
    public function getPlayerGroups($id): array;

    /**
     * Get a player's tracking details
     * @param mixed $id External ID for the player
     * @param Carbon $startDate
     * @param Carbon $endDate
     * @param string $statType
     * @return array
     */
    public function getPlayerTracking($extPlayerId, Carbon $startDate = null, Carbon $endDate = null, string $statType = null): array;

    /**
     * Convert the balance of a player's points to dollars
     *
     * @param  mixed $rankId The player's rank/tier ID
     * @param  float|integer $balance Points balance
     * @param  string $prefix  Want to prefix it with dollar sign... or something else?
     * @return string
     */
    public function convertPointsBalanceToDollars($rankId, $balance, $prefix = ''): string;

    /**
     * Update the player's profile info.
     *
     * @param Player $player The player object
     * @param array  $params An array that contains the updated profile data
     * @return bool
     **/
    public function updatePlayerProfile(Player $player, array $params = []) : bool;

}
