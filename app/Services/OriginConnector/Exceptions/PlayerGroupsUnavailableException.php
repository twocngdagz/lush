<?php

namespace App\Services\OriginConnector\Exceptions;

class PlayerGroupsUnavailableException extends \Exception
{
    protected $code = 404;
    protected $message = 'Player groups for this player could not be found.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($message ?? $this->message, $this->code);
    }
}
