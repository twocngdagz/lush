<?php

namespace App\Services\OriginConnector\Exceptions;

class LockedPinException extends \Exception
{
    protected $code = 401;
    protected $message = 'Account locked. Please see attendant to reset PIN.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
