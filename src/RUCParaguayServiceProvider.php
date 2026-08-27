<?php

namespace martinpa13py\RUCParaguay;

use Illuminate\Support\ServiceProvider;
use martinpa13py\RUCParaguay\Console\Commands\RucParaguayCmdSearch;
use martinpa13py\RUCParaguay\Console\Commands\RucParaguayCmdUpdate;
use martinpa13py\RUCParaguay\Services\RUCParaguay;
use martinpa13py\RUCParaguay\Services\RUCParaguayUpdater;

class RUCParaguayServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            RucParaguayCmdUpdate::class,
            RucParaguayCmdSearch::class,
        ]);

        $this->loadMigrationsFrom(__DIR__ . '/database/migrations/');

        $this->publishes([
            __DIR__ . '/../config/ruc-paraguay.php' => config_path('ruc-paraguay.php'),
        ], 'ruc-paraguay-config');
    }

    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ruc-paraguay.php', 'ruc-paraguay');

        $this->app->bind('RUCParaguay', function () {
            return $this->app->make(RUCParaguay::class);
        });

        $this->app->singleton(RUCParaguayUpdater::class, function () {
            return new RUCParaguayUpdater();
        });
    }
}
