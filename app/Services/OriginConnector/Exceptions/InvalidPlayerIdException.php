<?php

namespace App\Services\OriginConnector\Exceptions;

class InvalidPlayerIdException extends \Exception
{
    protected $code = 400;
    protected $message = 'Player ID could not be found.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
