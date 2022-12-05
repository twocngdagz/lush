<?php

namespace App\Services\OriginConnector;

class ConnectionException extends \Exception
{
    public function __toString() {
        return __CLASS__ . ": [{$this->code}]: {$this->message}\n";
    }
}
