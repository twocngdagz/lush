<?php

namespace App\Services\OriginConnector\Exceptions;

class PlayerIssueOfferException extends \Exception
{
    protected $code = 404;
    protected $message = 'Unable to issue offer to player.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
