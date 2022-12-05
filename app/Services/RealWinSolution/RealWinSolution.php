<?php

namespace App\Services\RealWinSolution;

use App\EarningMethodType;
use App\Services\RealWinSolution\Contracts\WinInterface;
use App\Services\RealWinSolution\Responses\KioskMethod;
use GuzzleHttp\Exception\ConnectException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RealWinSolution
{

    /**
     * @var WinInterface
     */
    private $client;

    private $key = 'other';


    public function __construct(WinInterface $client)
    {
        $this->client = $client;

    }//end __construct()


    public function getEarningMethods()
    {
        try {
            return cache()->remember(
                'realwin_earning_method',
                now()->addMinutes(5),
                function () {
                    return collect($this->client->KioskMethod())->map(
                        function ($item) {
                            return new KioskMethod(
                                [
                                    'name' => $item['Method'],
                                ]
                            );
                        }
                    );
                }
            );
        } catch (ConnectException $e) {
            logger()->error('RealWin Solutions Connection Timeout');
        } catch (\Throwable $e) {
            logger()->error('RealWin Solution has encountered an error: '.$e->getMessage());
        }//end try

    }//end getEarningMethods()


    public function getRatings()
    {
        return $this->getEarningMethods()->map(
            function ($method) {
                return sprintf('%s-%s', $this->key, Str::slug(str_replace('_', ' ', strtolower($method->name)), '-'));
            }
        );

    }//end getRatings()

    public function getPlayerScore($playerId)
    {
        try {
            return cache()->remember(
                sprintf("realwin_player_score_%s", $playerId),
                now()->addMinutes(5),
                function () use ($playerId) {
                    return $this->client->MTPlayerScore($playerId);
                }
            );
        } catch (ConnectException $e) {
            logger()->error('RealWin Solutions Connection Timeout');
        } catch (\Throwable $e) {
            logger()->error('RealWin Solution has encountered an error: '.$e->getMessage());
        }//end try
    }


}//end class
