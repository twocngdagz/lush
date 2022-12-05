<?php

namespace App\Services\OriginConnector\Exceptions;

class GetIdentificationTypesException extends \Exception
{
    protected $code = 500;
    protected $message = 'The Loyalty Rewards system could not collect the list of valid player identification types.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
