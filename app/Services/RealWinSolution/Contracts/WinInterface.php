<?php

namespace App\Services\RealWinSolution\Contracts;

use App\Services\RealWinSolution\Responses\ValidatePlayer;
use Illuminate\Support\Collection;

interface WinInterface
{
    public function validatePlayer() : ValidatePlayer;
    public function kioskGroup() : Collection;
    public function kioskGroupPlayer(int $playerId, int $groupId) : bool;
    public function KioskMethod() : array;
    public function KioskMethodPlayer($playerId, $method) : int;
    public function KioskOffer($playerId) : Collection;
    public function KioskOfferRedeem($guid) : int;
    public function MTPlayerScore($playerid) : int;
}