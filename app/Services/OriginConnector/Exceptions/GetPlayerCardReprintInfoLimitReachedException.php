<?php

namespace App\Services\OriginConnector\Exceptions;

class GetPlayerCardReprintInfoLimitReachedException extends \Exception
{
    protected $code = 401;
    protected $message = 'The daily maximum number of card reprints has been reached.';

    function __construct($message = null, $code = null)
    {
        $message = $message ?? $this->message;
        $code = $code ?? $this->code;
        parent::__construct($message, $code, $this);
    }
}
