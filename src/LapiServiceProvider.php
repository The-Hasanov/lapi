<?php

namespace Lapi;

use Illuminate\Support\ServiceProvider;
use Lapi\Response\ApiResponse;

class LapiServiceProvider extends ServiceProvider
{

    public function boot(): void
    {
        $this->publishes([$this->configPath() => $this->app->configPath('api.php')], 'api');

        foreach ($this->app['config']->get('api.formatters', []) as $formatter) {
            ApiResponse::addFormatter($this->app->make($formatter));
        }
    }

    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'api');
    }

    private function configPath()
    {
        return __DIR__ . '/../config/api.php';
    }

}