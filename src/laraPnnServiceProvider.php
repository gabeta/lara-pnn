<?php

namespace Gabeta\LaraPnn;


use Gabeta\LaraPnn\Console\LaraPnnMigrateCommand;
use Gabeta\LaraPnn\Console\LaraPnnRollbackCommand;
use Illuminate\Support\ServiceProvider;

class laraPnnServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/larapnn.php', 'larapnn');

        $this->app->bind('laraPnn', function ($app) {
            return new LaraPnn();
        });
    }

    public function boot()
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                LaraPnnMigrateCommand::class,
                LaraPnnRollbackCommand::class
            ]);
        }

        $this->publishes([
            __DIR__.'/../config/larapnn.php' => config_path('larapnn.php'),
        ], 'larapnn-config');
    }
}
