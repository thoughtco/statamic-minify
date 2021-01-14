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
}
