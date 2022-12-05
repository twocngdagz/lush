<?php

namespace App\Services\OriginConnector\Exceptions;

class PlayerBalanceInfluenceException extends \Exception
{
    protected $code = 401;
    protected $message = 'There was an error updating this player\'s account balance.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
