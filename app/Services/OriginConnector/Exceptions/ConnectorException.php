<?php

namespace App\Services\OriginConnector\Exceptions;

class ConnectorException extends \Exception
{
    protected $code = 500;
    protected $message = 'Origin Connector Identifier not recognized.';

    function __construct($message = null, $code = null)
    {
        $this->message = $message ?: $this->message;
        $this->code = $code ?: $this->code;
        parent::__construct($this->message, $this->code);
    }
}
