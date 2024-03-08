<?php

namespace Thoughtco\Minify;

use Illuminate\Support\Facades\File;
use Statamic\Providers\AddonServiceProvider;
use Statamic\Statamic;
use Thoughtco\Minify\Middleware\MinifyMiddleware;

class ServiceProvider extends AddonServiceProvider
{
    protected $middlewareGroups = [
        'web' => [
            MinifyMiddleware::class,
        ],
    ];

    public function boot()
    {
        parent::boot();

        Statamic::afterInstalled(function ($command) {

            if (File::exists(config_path('thoughtco/minify.php')))
                return;

            if (!File::isDirectory(config_path('thoughtco')))
                File::makeDirectory(config_path('thoughtco'), 0775, true, true);

            File::copy( __DIR__.'/../config/minify.php', config_path('thoughtco/minify.php'));

        });

    }
}
