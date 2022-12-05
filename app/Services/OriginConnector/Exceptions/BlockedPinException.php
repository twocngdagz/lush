<?php

namespace App\Services\OriginConnector\Exceptions;

class BlockedPinException extends \Exception
{
    protected $code = 400;
    protected $message = 'The PIN number provided may have been blocked from multiple incorrect entries.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
