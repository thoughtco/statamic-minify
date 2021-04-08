<?php

namespace Thoughtco\Minify;

use File;
use Statamic\Providers\AddonServiceProvider;

class ServiceProvider extends AddonServiceProvider
{
    protected $listen = [
        'Statamic\Events\ResponseCreated' => [
            'Thoughtco\Minify\Listeners\MinifyListener',
        ],
    ];

    protected $publishables = [
        __DIR__.'/../config/minify.php' => 'config/thoughtco'
    ];

    public function boot()
    {
        if (!File::exists(config_path('thoughtco'))) {
            File::makeDirectory(config_path('thoughtco'), 0777, true, true);
        }

        parent::boot();
    }
}
