<?php

/**
 * This API trait contains functionality related to player earnings for the
 * Phi Mock.
 */

namespace App\Services\OriginConnector\Providers\PhiMock\Traits;

use App\Player;
use App\Services\OriginConnector\Exceptions\PlayerAccountRedemptionException;

trait PlayerRedemptionTrait
{
    public function redeemPoints(
        Player $player,
        int $amount,
        string $comments = '',
        int $propertyId = 1,
        array $options = [] // add options for specific origin connector
    ) {
        try {
            // Get the account type for the accountName
            $account = $this->getPlayerAccounts($player->ext_id)->firstWhere('internal_identifier', 'points');
            $data = [
                'amount' => $amount,
                'comments' => $comments,
            ];

            // Make the call
            $result = $this->post("/players/{$player->ext_id}/accounts/$account->id/redeem", $data);

            return true;
        } catch (\Exception $ex) {
            throw new PlayerAccountRedemptionException($player->id, $ex->getCode(), $ex);
        }
    }
}
