<?php

namespace App\Services\OriginConnector\Exceptions;

class PlayerExcludedException extends \Exception
{
    protected $code = 400;
    protected $message = 'The player you are looking for has been excluded and is unavailable.';

    function __construct($playerId = null, $errorCode = null)
    {
        if ($errorCode) {
            switch ($errorCode) {
                case 'M15':
                    $this->message = 'Player is marked as Excluded';
                    break;
                case 'M16':
                    $this->message = 'Player is marked as Deceased';
                    break;
                case 'M17':
                    $this->message = 'Player is a Merge Victim';
                    break;
            }
        }

        if ($playerId) {
            $this->message .= ' : ID ' . $playerId;
        }

        parent::__construct($this->message, $this->code);
    }
}
