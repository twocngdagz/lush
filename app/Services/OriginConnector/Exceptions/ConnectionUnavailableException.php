<?php

namespace App\Services\OriginConnector\Exceptions;

class ConnectionUnavailableException extends \Exception
{
    protected $code = 503;
    protected $message = 'The connection to the Player Gateway is unavailable.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
