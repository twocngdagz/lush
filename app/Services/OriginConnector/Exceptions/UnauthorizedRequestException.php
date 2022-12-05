<?php

namespace App\Services\OriginConnector\Exceptions;

class UnauthorizedRequestException extends \Exception
{
    protected $code = 401;
    protected $message = 'Authorization has been denied for this request.';

    function __construct($message = null)
    {
        $this->message = $message ?? $this->message;
        parent::__construct($this->message, $this->code);
    }
}
