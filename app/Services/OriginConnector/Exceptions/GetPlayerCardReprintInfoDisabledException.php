<?php

namespace App\Services\OriginConnector\Exceptions;

class GetPlayerCardReprintInfoDisabledException extends \Exception
{
    protected $code = 400;
    protected $message = 'Loyalty card reprinting has been disabled.';

    function __construct($message = null, $code = null)
    {
        $message = $message ?? $this->message;
        $code = $code ?? $this->code;
        parent::__construct($message, $code, $this);
    }
}
