<?php

namespace App\Services\RealWinSolution\Responses;

use App\Services\RealWinSolution\DataTransferObject;

class KioskMethod extends DataTransferObject
{
    public $name;

    public static function createFromArray(array $data)
    {
        return new self([
            'name' => $data['name']
        ]);
    }
}