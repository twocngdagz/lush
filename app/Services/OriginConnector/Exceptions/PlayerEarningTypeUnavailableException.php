<?php

namespace App\Services\OriginConnector\Exceptions;

class PlayerEarningTypeUnavailableException extends \Exception
{
    protected $code = 404;
    protected $message = 'The earning type lookup for this player could not be found.';

    function __construct($message = null, $code = null, $prev = null)
    {
        $this->message = $message ?: $this->message;
        $this->code = $code ?: $this->code;
        parent::__construct($this->message, $this->code, $prev);
    }
}
