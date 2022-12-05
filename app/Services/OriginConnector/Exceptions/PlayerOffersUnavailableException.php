<?php

namespace App\Services\OriginConnector\Exceptions;

class PlayerOffersUnavailableException extends \Exception
{
    protected $code = 404;
    protected $message = 'Player offers for this player are unavailable.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
