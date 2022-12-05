<?php

namespace App\Services\RealWinSolution;

use App\Services\RealWinSolution\Commands\SyncRealWinConnection;
use App\Services\RealWinSolution\Commands\WinConnection;
use App\Services\RealWinSolution\Contracts\WinInterface;
use Illuminate\Support\ServiceProvider;

class RealWinSolutionServiceProvider extends ServiceProvider
{
    public function boot()
    {
        $this->app->bind(WinInterface::class, Client::class);
        $this->app->bind(RealWinSolution::class, function () {
            return new RealWinSolution(new Client);
        });

        $this->commands([
            SyncRealWinConnection::class,
            WinConnection::class
        ]);
    }
}