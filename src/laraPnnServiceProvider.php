<?php

namespace Gabeta\LaraPnn;


use Illuminate\Support\ServiceProvider;

class laraPnnServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/larapnn.php', 'larapnn');
    }

    public function boot()
    {
        $this->publishes([
            __DIR__.'/../config/larapnn.php' => config_path('larapnn.php'),
        ], 'larapnn-config');
    }
}
