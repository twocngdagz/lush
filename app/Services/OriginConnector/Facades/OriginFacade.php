<?php

namespace App\Services\OriginConnector\Facades;

use App\Services\OriginConnector\Connector;
use Illuminate\Support\Facades\Facade;

class OriginFacade extends Facade
{
    protected static function getFacadeAccessor()
    { return Connector::class; }
}
