<?php

namespace App\Services\OriginConnector\Providers\PhiMock;

use Blade;
use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Scheduling\Schedule;

use App\Services\OriginConnector\Providers\PhiMock\Commands\DeleteWinLossReports;
use App\Services\OriginConnector\Providers\PhiMock\Commands\SyncOriginProperties;
use App\Services\OriginConnector\Providers\PhiMock\Commands\SyncOriginPlayerNames;

class ConnectorServiceProvider extends ServiceProvider
{
    /**
     * Register connector specific services.
     *
     * @return void
     */
    public function register()
    {
    }

    /**
     * Bootstrap the connector - Load connector specific views,
     * routes, jobs, scheduled tasks, console commands, etc.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerTranslations();
        $this->registerViews();
        $this->registerCommands();
        $this->registerSchedule();
        $this->registerBladeDirectives();
    }

    /**
     * Registers routes specific to OLKG
     *
     * @return void
     */
    public function registerRoutes()
    {
        // $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
//        $this->loadRoutesFrom(__DIR__ . '/Routes/api.php');
        // $this->loadRoutesFrom(__DIR__.'/Routes/tests.php');
    }

    /**
     * Registers DB Migrations.
     */
    private function registerMigrations()
    {
        $this->loadMigrationsFrom(__DIR__ . '/Database/Migrations');
    }

    /**
     * Registers language translations files.
     */
    private function registerTranslations()
    {
        $this->loadTranslationsFrom(__DIR__ . '/Resources/Lang', 'origin');
    }

    /**
     * Registers Blade views.
     */
    private function registerViews()
    {
        $this->loadViewsFrom(__DIR__ . "/Resources/Views", 'origin');
    }

    /**
     * Registers Artisan commands.
     */
    private function registerCommands()
    {
        $this->commands([
            SyncOriginProperties::class,
        ]);
    }

    /**
     * Registers scheduled commands/jobs.
     *
     * @return void
     */
    private function registerSchedule()
    {
        $schedule = new Schedule();
    }

    /**
     * Registers Blade directives.
     */
    private function registerBladeDirectives()
    {
    }

}
