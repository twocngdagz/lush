<?php

/**
 * This trait holds common connector settings methods.
 */
namespace App\Services\OriginConnector\Traits;


trait ConnectionSettingsTrait
{
    /**
     * Can the origin connect to the player?
     * @param int $playerId The player ID to test.
     * @return boolean
     */
    public function canGetPlayer($playerId): ?bool
    {
        try {
            Origin::getPlayer($playerId);
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Can this origin connect to the player accounts?
     * @param int $playerId The player ID to test.
     * @return boolean
     */
    public function canGetPlayerAccounts($playerId): ?bool
    {
        try {
            return Origin::getPlayerAccounts($playerId);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Get property information
     * @return boolean
     */
    public function canGetPropertyInfo(): ?bool
    {
        try {
            return Origin::getPropertyInfo();
        } catch (\Exception $e) {
            return false;
        }
    }

}
