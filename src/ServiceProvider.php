<?php

namespace Thoughtco\Minify;

use Illuminate\Support\Facades\File;
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

        Statamic::afterInstalled(function ($command) {

            if (!File::isDirectory(config_path('thoughtco')))
                File::makeDirectory(config_path('thoughtco'), 0775, true, true);

            File::copy( __DIR__.'/../config/minify.php', config_path('thoughtco/minify.php'));

        });

    }
}
