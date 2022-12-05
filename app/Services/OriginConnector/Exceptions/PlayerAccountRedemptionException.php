<?php

namespace App\Services\OriginConnector\Exceptions;

class PlayerAccountRedemptionException extends \Exception
{
    protected $code = 400;
    protected $message = "There was an error redeeming points.";

    function __construct($playerId = null)
    {
        if ($playerId) {
            $this->message .= " : PlayerID=$playerId";
        }

        parent::__construct($this->message, $this->code);
    }
}
