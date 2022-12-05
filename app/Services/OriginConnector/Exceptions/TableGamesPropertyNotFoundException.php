<?php

namespace App\Services\OriginConnector\Exceptions;

class TableGamesPropertyNotFoundException extends \Exception
{
    protected $code = 404;
    protected $message = 'Property not Found in TableGameCodes';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
