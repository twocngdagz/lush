<?php

namespace App\Services\OriginConnector\Exceptions;

class PlayerAccountNotFoundException extends \Exception
{
    protected $code = 404;
    protected $message = 'The player account requested was not found on this installation.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
