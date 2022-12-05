<?php

namespace App\Services\OriginConnector\Providers\Lush;


use App\Services\OriginConnector\Providers\Lush\Models\LushAccount;
use App\Services\OriginConnector\Providers\Lush\Models\LushPlayer;
use App\Services\OriginConnector\Providers\Lush\Models\LushRating;
use App\Services\OriginConnector\Providers\Lush\Commands\SyncOriginProperties;
use App\Services\OriginConnector\Providers\Lush\Observers\LushAccountObserver;
use App\Services\OriginConnector\Providers\Lush\Observers\LushPlayerObserver;
use App\Services\OriginConnector\Providers\Lush\Observers\LushRatingObserver;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\ServiceProvider;

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
//        $this->registerTranslations();
        $this->registerViews();
        $this->registerCommands();
//        $this->registerSchedule();
        $this->registerBladeDirectives();
    }

    /**
     * Registers routes specific to OLKG
     *
     * @return void
     */
    public function registerRoutes()
    {
         $this->loadRoutesFrom(__DIR__.'/Routes/web.php');
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
        Blade::directive('is_cms_lush', function () {
            return "<?php if (isCMSLush()) { ?>";
        });

        Blade::directive('is_cms_not_lush', function () {
            return "<?php if (!isCMSLush()) { ?>";
        });

        Blade::if('envIsNotProduction', function () {
            return app()->environment() !== 'production';
        });

        Blade::directive('end_is_cms_lush', function() {
            return "<?php } ?>";
        });

        Blade::directive('end_is_cms_not_lush', function() {
            return "<?php } ?>";
        });

        LushPlayer::observe(LushPlayerObserver::class);
        LushRating::observe(LushRatingObserver::class);
        LushAccount::observe(LushAccountObserver::class);
    }
}
