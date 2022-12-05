<?php

namespace App\Services\OriginConnector\Exceptions;

class GetPlayerCardReprintInfoNotFoundException extends \Exception
{
    protected $code = 404;
    protected $message = 'The Loyalty Rewards system could not retrieve card reprint information for the given identification number.';

    function __construct($message = null, $code = null)
    {
        $message = $message ?? $this->message;
        $code = $code ?? $this->code;
        parent::__construct($message, $code, $this);
    }
}
