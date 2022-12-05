<?php

namespace App\Services\OriginConnector\Exceptions;

class PlayerActivityUnavailableException extends \Exception
{
    protected $code = 401;
    protected $message = 'Activity for the provided date range is unavailable for this player.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
