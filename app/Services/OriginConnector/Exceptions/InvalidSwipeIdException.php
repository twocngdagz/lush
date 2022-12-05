<?php

namespace App\Services\OriginConnector\Exceptions;

class InvalidSwipeIdException extends \Exception
{
    protected $code = 400;
    protected $message = 'The card used has an invalid swipe identifier.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
