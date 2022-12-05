<?php

namespace App\Services\OriginConnector\Exceptions;

class InvalidPinException extends \Exception
{
    protected $code = 401;
    protected $message = 'The pin number provided was incorrect for this player.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
