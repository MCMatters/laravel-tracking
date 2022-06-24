<?php

declare(strict_types=1);

namespace McMatters\LaravelTracking;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

/**
 * Class ServiceProvider
 *
 * @package McMatters\LaravelTracking
 */
class ServiceProvider extends BaseServiceProvider
{
    /**
     * @return void
     */
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/tracking.php' => $this->app->configPath('tracking.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../migrations' => $this->app->databasePath('migrations'),
            ], 'migrations');

            $this->commands([
                Console\Commands\PruneCommand::class,
            ]);
        }
    }

    /**
     * @return void
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/tracking.php', 'tracking');
    }
}
