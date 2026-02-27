<?php

namespace Pmm\Xarife;

use Illuminate\Support\ServiceProvider;
use Pmm\Xarife\Commands\MakeOrgao;

class XarifeServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                MakeOrgao::class,
            ]);

            $this->publishes([
                __DIR__.'/../stubs' => base_path('stubs/xarife'),
            ], 'xarife-stubs');
        }
    }
}
