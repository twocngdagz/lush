<?php

namespace App\Services\RealWinSolution\Responses;

use App\Services\RealWinSolution\DataTransferObject;
use GuzzleHttp\Psr7\Response;

class KioskGroup extends DataTransferObject
{
    public $id;
    public $description;

    public static function createFromArray(array $data)
    {
        return new self([
            'id' => $data['GroupID'],
            'description' => $data['GroupDesc']
        ]);
    }
}