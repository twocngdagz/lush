<?php

namespace App\Services\OriginConnector\Providers\Lush\Transformers;

use Carbon\Carbon;
use League\Fractal\TransformerAbstract;

class LushPlayerTransformer extends TransformerAbstract
{
    public function transform($player)
    {
        $birth_date = Carbon::parse($player->birthday);
        $ret = [
            'id' => $player->id,
            'card_id' => $player->id,
            'profile' => [
                'first_name' => $player->first_name,
                'last_name' => $player->last_name,
                'full_name' => $player->name,
                'birth_date' => $birth_date->format('Y-m-d'),
                'age' => $birth_date->age,
                'gender' => $player->gender,
                'property_id' => null,
                'property' => null,
                'registered_property' => null,
                'registered_property_id' => null,
                'registered_date' => Carbon::parse($player->registered_at)->format('Y-m-d'),
                'days_since_registration' => Carbon::parse($player->registered_at)->diffInDays(now()),
                'email' => $player->email,
                'email_opt_in' => $player->email_opt_in,
                'phone' => $player->phone,
                'phone_opt_in' => $player->phone_opt_in,
                'phone_country' => 'US',
                'address' => null,
                'dap' => null,
                'merged' => null,
                'deceased' => null,
                'PlayerEmailList' => null,
                'PlayerPhoneList' => null,
                'PlayerAddressList' => null,
                'ext_id' => $player->id,
            ],
            'rank' => [
                'id' => $player->lushrank->id,
                'name' => $player->lushrank->name
            ]
        ];

        // Collect the player's address
        if (!empty($player->address)) {
            $ret['profile']['address'] = $this->transformAddress((object)collect($player)->only(['address', 'address_2', 'city', 'state', 'zip', 'country'])->toArray());
        }

        return $ret;
    }

    private function transformAddress($addressRow)
    {
        return [
            'full_address' => $this->buildAddressString($addressRow),
            'line1' => $addressRow->address ?? '',
            'line2' => $addressRow->address_2 ?? '',
            'city' => $addressRow->city ?? '',
            'state_code' => $addressRow->state ?? '',
            'postcode' => $addressRow->zip ?? '',
            'country_code' => $addressRow->country ?? '',
        ];
    }

    private function buildAddressString($addressRow)
    {
        $address_parts = collect([
            collect([$addressRow->address ?? '', $addressRow->address_2 ?? ''])->filter()->implode(' '),
            $addressRow->city ?? '',
            $addressRow->state ?? '',
            $addressRow->zip ?? '',
            $addressRow->country ?? '',
        ]);

        return $address_parts->filter()->implode(', ');
    }
}
