<?php

namespace App\Services\OriginConnector\Exceptions;

class PlayerBucketAwardsUnavailableException extends \Exception
{
    protected $code = 404;
    protected $message = 'Bucket awards for this player are unavailable.';

    function __construct($message = null)
    {
        $this->message = $message ?: $this->message;
        parent::__construct($this->message, $this->code);
    }
}
