<?php

namespace App\Services\RealWinSolution\Responses;

use App\Services\RealWinSolution\DataTransferObject;
use GuzzleHttp\Psr7\Response;

class ValidatePlayer extends DataTransferObject
{
    public $id;
    public $firstname;
    public $lastname;
    public $email;
    public $tiername;
    public $tierlevel;

    public static function createFromResponse(Response $response)
    {
        $data = json_decode((string) $response->getBody(), true);

        return new self([
            'id' => $data['playerinfo'][0]['PlayerID'],
            'firstname' => $data['playerinfo'][0]['FirstName'],
            'lastname' => $data['playerinfo'][0]['LastName'],
            'email' => $data['playerinfo'][0]['Email'],
            'tiername' => $data['playerinfo'][0]['TierName'],
            'tierlevel' => $data['playerinfo'][0]['TierLevel'],
        ]);
    }
}