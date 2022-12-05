<?php

namespace App\Services\OriginConnector\Exceptions;

class UncaughtOriginRequestException extends \Exception
{
    protected $code = 500;
    protected $message = 'There was an uncaught error with this request to the Origin Connector.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
