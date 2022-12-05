<?php

namespace App\Services\OriginConnector\Exceptions;

class InvalidIdentificationTypeException extends \Exception
{
    protected $code = 400;
    protected $message = 'Could not recognize the scanned document type.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
