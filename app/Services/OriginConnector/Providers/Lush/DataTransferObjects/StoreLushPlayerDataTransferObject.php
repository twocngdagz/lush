<?php

namespace App\Services\OriginConnector\Providers\Lush\DataTransferObjects;

use App\Services\RealWinSolution\DataTransferObject;
use Illuminate\Support\Carbon;
use function GuzzleHttp\Promise\queue;

class StoreLushPlayerDataTransferObject extends DataTransferObject
{
    public $first_name;
    public $middle_initial;
    public $last_name;
    public $birthday;
    public $gender;
    public $lush_rank_id;
    public $id_type;
    public $id_number;
    public $id_expiration_date;
    public $email;
    public $phone;
    public $address;
    public $address_2;
    public $city;
    public $state;
    public $zip;
    public $country;
    public $email_opt_in = false;
    public $phone_opt_in = false;
    public $is_excluded = false;

    public static function fromArray(array $data): self {

        $object = new self([
            'first_name' => $data['first_name'],
            'middle_initial' => $data['middle_initial'],
            'last_name' => $data['last_name'],
            'birthday' => Carbon::parse($data['birthday']),
            'gender' => $data['gender'],
            'lush_rank_id' => $data['lush_rank_id'],
            'id_type' => $data['id_type'],
            'id_number' => $data['id_number'],
            'id_expiration_date' => Carbon::parse($data['id_expiration_date']),
            'email' => $data['email'],
            'phone' => $data['phone'],
            'address' => $data['address'],
            'address_2' => $data['address_2'],
            'city' => $data['city'],
            'state' => $data['state'],
            'zip' => $data['zip'],
            'country' => $data['country'] ?? null
        ]);

        if(in_array('email_opt_in', $data)) {
            $object->email_opt_in = $data['email_opt_in'];
        }
        if(in_array('phone_opt_in', $data)) {
            $object->phone_opt_in = $data['phone_opt_in'];
        }
        if (in_array('card_pin', $data)) {
            $object->card_pin = $data['card_pin'];
        }
        if(in_array('card_pin_attempts', $data)) {
            $object->card_pin_attempts = $data['card_pin_attempts'];
        }
        if(in_array('is_excluded', $data)) {
            $object->is_excluded = $data['is_excluded'];
        }
        if ($data['register_at_date'] && $data['register_at_time']) {
            $object->registered_at =  Carbon::createFromFormat('m/d/Y H:i', $data['register_at_date'] . ' ' . $data['register_at_time']);
        }

        return $object;

    }

}
