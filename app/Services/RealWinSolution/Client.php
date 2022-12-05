<?php

namespace App\Services\RealWinSolution;

use App\Services\RealWinSolution\Contracts\WinInterface;
use App\Services\RealWinSolution\Models\RealWinConnection;
use App\Services\RealWinSolution\Responses\KioskGroup;
use App\Services\RealWinSolution\Responses\KioskOffer;
use App\Services\RealWinSolution\Responses\ValidatePlayer;
use App\Services\RealWinSolution\Transformers\KioskGroupTransformer;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Collection;

class Client implements WinInterface
{
    protected $baseUrl;
    protected $username;
    protected $password;

    public function __construct()
    {
        $this->baseUrl = RealWinConnection::latest()->first()->url;
        $this->username = config('realwin_solution.username');
        $this->password = config('realwin_solution.password');

        $this->client = new HttpClient([
            'base_uri' => $this->baseUrl,
            'timeout'  => 10,
        ]);

    }

    public function validatePlayer($playerId = 1234, $cred = '03-04-2020') : ValidatePlayer
    {
        try {
            $response = $this->client->request('get', '/api/procedures/validatePlayer', [
                'auth' => [
                    $this->username,
                    $this->password
                ],
                'query' => [
                    'playerID' => $playerId,
                    'cred' => $cred
                ]
            ]);
            return ValidatePlayer::createFromResponse($response);
        } catch (\Throwable $e) {
            logger()->error('Error RealWinSolution: ' . $e->getMessage());
            return ValidatePlayer::createFromResponse(new Response());
        }
    }

    public function kioskGroup() : Collection
    {
        $response = $this->client->request('get', '/api/procedures/api_KioskGroup', [
            'auth' => $this->getAuth()
        ]);

        $data = json_decode((string) $response->getBody(), true);
        return collect($data['Groupinfo'])->map(function ($item) {
            return KioskGroup::createFromArray($item);
        });
    }

    public function kioskGroupPlayer(int $playerId, int $groupId) : bool
    {
        $response = $this->client->request('get', '/api/procedures/api_KioskGroupPlayer', [
            'auth' => $this->getAuth(),
            'query' => [
                'playerID' => $playerId,
                'groupID' => $groupId
            ]
        ]);

        $data = json_decode((string) $response->getBody(), true);
        return !($data['GroupMember'][0]['Response'] === "N");
    }

    public function KioskMethod() : array
    {
        $response = $this->client->request('get', '/api/procedures/api_KioskMethod', [
            'auth' => $this->getAuth()
        ]);

        $data = json_decode((string) $response->getBody(), true);
        return $data['Methodinfo'];
    }

    public function KioskMethodPlayer($playerId, $method) : int
    {
        $response = $this->client->request('get', '/api/procedures/api_KioskMethodPlayer', [
            'auth' => $this->getAuth(),
            'query' => [
                'playerID' => $playerId,
                'method' => $method
            ]
        ]);

        $data = json_decode((string) $response->getBody(), true);
        return $data['MethodValue'][0]['Value'];
    }

    public function KioskOffer($playerId) : Collection
    {
        $response = $this->client->request('get', '/api/procedures/api_KioskOffer', [
            'auth' => $this->getAuth(),
            'query' => [
                'playerID' => $playerId
            ]
        ]);

        $data = json_decode((string) $response->getBody(), true);
        return collect($data['Offers'])->map(function ($item) {
            return KioskOffer::createFromArray($item);
        });
    }

    public function KioskOfferRedeem($guid) : int
    {
        $response = $this->client->request('get', '/api/procedures/api_KioskOfferRedeem', [
            'auth' => $this->getAuth(),
            'query' => [
                'guid' => $guid
            ]
        ]);

        $data = json_decode((string) $response->getBody(), true);
        return $data['OfferRedeem'][0]['ID'];
    }

    protected function getAuth() : array
    {
        return [
            $this->username,
            $this->password
        ];
    }

    public function MTPlayerScore($playerId): int
    {
        $response = $this->client->request('get', '/api/procedures/api_MTPlayerScore', [
            'auth' => $this->getAuth(),
            'query' => [
                'playerID' => $playerId
            ]
        ]);

        $data = json_decode((string) $response->getBody(), true);
        return (int) $data['Scores'][0]['Score'];
    }
}