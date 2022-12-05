<?php

namespace App\Services\OriginConnector\Exceptions;

class UnknownCardNumberException extends \Exception
{
    protected $code = 404;
    protected $message = 'This card could not be identified for this location.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
