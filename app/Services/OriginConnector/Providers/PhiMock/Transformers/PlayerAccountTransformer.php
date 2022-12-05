<?php

namespace App\Services\OriginConnector\Providers\PhiMock\Transformers;

use App\Property;
use League\Fractal\TransformerAbstract;

class PlayerAccountTransformer extends TransformerAbstract
{

    public function transform($account)
    {
        $options = Property::find(config('services.connector.property_id'))->balanceDisplayOptionsFor($account->type);
        $amount = $options['decimals'] > 0
            ? (float)str_replace(',', '', $account->balance)
            : (int)str_replace(',', '', $account->balance);

        $ret = [
            'id' => $account->id,
            'internal_identifier' => $options['internal_identifier'],
            'name' => $account->name,
            'show_on_kiosk' => $options['show_on_kiosk'],
            'label' => $options['label'],
            'type_id' => $account->type,
            'amount' => $amount,
            'balance' => number_format($amount, $options['decimals']),
            'currency' => $options['currency'],
            'sort' => $options['sort'],
        ];

        return $ret;
    }
}
