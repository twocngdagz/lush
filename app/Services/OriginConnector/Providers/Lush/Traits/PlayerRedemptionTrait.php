<?php

namespace App\Services\OriginConnector\Providers\Lush\Traits;

use App\Services\OriginConnector\Providers\Lush\Models\LushAccount;
use App\Player;
use App\Services\OriginConnector\Exceptions\PlayerAccountRedemptionException;
use Illuminate\Support\Facades\Validator;

trait PlayerRedemptionTrait
{
    public function redeemCompPoints(
        Player $player,
        int $amount,
        string $comments = '',
        int $propertyId = 1,
        array $options = [] // add options for specific origin connector
    ) {
        try {
            // Get the account type for the accountName
            $account = $this->getPlayerAccounts($player->ext_id)->firstWhere('internal_identifier', 'comps');

            $data = [
                'amount' => $amount,
                'comments' => $comments,
            ];

            $validatedData = Validator::make($data, [
                'amount' => 'required'
            ])->validate();

            $lushAccount = LushAccount::findOrFail($account->id);
            $amount = ($lushAccount->is_currency) ? $validatedData['amount'] * 100 : $validatedData['amount'];
            $lushAccount->decrement('balance', $amount);

            return true;
        } catch (\Exception $ex) {
            throw new PlayerAccountRedemptionException($player->id, $ex->getCode(), $ex);
        }
    }

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

            $validatedData = Validator::make($data, [
                'amount' => 'required'
            ])->validate();

            $lushAccount = LushAccount::findOrFail($account->id);
            $amount = ($lushAccount->is_currency) ? $validatedData['amount'] * 100 : $validatedData['amount'];
            $lushAccount->decrement('balance', $amount);

            return true;
        } catch (\Exception $ex) {
            throw new PlayerAccountRedemptionException($player->id, $ex->getCode(), $ex);
        }
    }
}
