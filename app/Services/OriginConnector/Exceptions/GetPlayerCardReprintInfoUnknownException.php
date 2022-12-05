<?php

namespace App\Services\OriginConnector\Exceptions;

class GetPlayerCardReprintInfoUnknownException extends \Exception
{
    protected $code = 500;
    protected $message = 'The Loyalty Rewards system encountered an unknown error. Please see the log file for additional information.';

    function __construct($message = null, $code = null)
    {
        $message = $message ?? $this->message;
        $code = $code ?? $this->code;
        parent::__construct($message, $code, $this);
    }
}
