<?php

namespace Thoughtco\Minify;

use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $listen = [
        'Statamic\Events\ResponseCreated' => [
            'Thoughtco\Minify\Listeners\MinifyListener',
        ],
    ];

    public function boot()
    {
        parent::boot();

        $this->publishes([
            __DIR__.'/../config/minify.php' => config_path('thoughtco/minify.php')
        ], 'config');
    }
}
