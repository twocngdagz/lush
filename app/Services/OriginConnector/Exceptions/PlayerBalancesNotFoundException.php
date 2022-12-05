<?php

namespace App\Services\OriginConnector\Exceptions;

class PlayerBalancesNotFoundException extends \Exception
{
    protected $code = 404;
    protected $message = 'Could not locate any account balances for the player.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
