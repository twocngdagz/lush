<?php

namespace App\Services\OriginConnector\Exceptions;

class PropertyNotFoundException extends \Exception
{
    protected $code = 404;
    protected $message = 'The Loyalty Rewards system could not connect to the property.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
