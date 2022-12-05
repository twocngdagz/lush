<?php

namespace App\Services\OriginConnector\Providers\PhiMock\Transformers;

use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use League\Fractal\TransformerAbstract;
use Propaganistas\LaravelPhone\PhoneNumber;

class PlayerTransformer extends TransformerAbstract
{

    public function transform($player)
    {
        $birth_date = Carbon::parse($player->birthday);
        $ret = [
            'id' => $player->id,
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
                'email_opt_in' => true,
                'phone' => $player->phone,
                'phone_opt_in' => true,
                'phone_country' => 'US',
                'address' => null,
                'dap' => null,
                'merged' => null,
                'deceased' => null,
                'PlayerEmailList' => null,
                'PlayerPhoneList' => null,
                'PlayerAddressList' => null,
            ],
            'rank' => [
                'id' => $player->rank->id,
                'name' => $player->rank->name
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
