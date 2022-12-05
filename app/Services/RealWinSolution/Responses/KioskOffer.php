<?php

namespace App\Services\RealWinSolution\Responses;

use App\Services\RealWinSolution\DataTransferObject;
use ArrayAccess;
use Illuminate\Contracts\Support\Arrayable;

class KioskOffer extends DataTransferObject implements Arrayable, ArrayAccess
{
    public $id;
    public $guid;
    public $name;
    public $description;
    public $amount;
    public $start_date;
    public $end_date;
    public $expires;
    public $has_criteria;
    public $offer_type;
    public $print_bar_code;

    public static function createFromArray(array $data)
    {
        return new self([
            'id' => $data['GUID'],
            'guid' => $data['GUID'],
            'name' => $data['OfferName'],
            'description' => $data['Description'],
            'amount' => $data['Amount'],
            'start_date' => $data['StartDate'],
            'end_date' => $data['EndDate'],
            'expires' => $data['Expires'],
            'has_criteria' => (bool) $data['HasCriteria'],
            'offer_type' => $data['OfferType'],
            'print_bar_code' => $data['PrintBarcode'],
        ]);
    }

    public function getKey()
    {
        return $this->guid;
    }

    public function isPrintable()
    {
        return $this->print_bar_code;
    }

    public function toArray()
    {
        return (array) $this;
    }

    public function toJson()
    {
        return json_encode($this->toArray(), true);
    }

    public function __toString() : string
    {
        return $this->toJson();
    }

    public function offsetExists($offset)
    {
        return ! is_null($this->{$offset});
    }

    public function offsetGet($offset)
    {
        return $this->{$offset};
    }

    public function offsetSet($offset, $value)
    {
        $this->{$offset} = $value;
    }

    public function offsetUnset($offset)
    {
        unset($this->{$offset});
    }
}