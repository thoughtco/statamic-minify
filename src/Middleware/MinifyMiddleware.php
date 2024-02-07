<?php

namespace Thoughtco\Minify\Middleware;

use Closure;
use Thoughtco\Minify\Managers\MinifyManager;

class MinifyMiddleware
{
	private $minPath = 'min/';
	private $ignoreUrls = [];

    /**
     * before response sent back to browser
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);

        if (! request()->is(config('statamic.cp.route').'/*') && ! request()->is('!/*')) {

            if (method_exists($response, 'content')) {

                $content = $response->content();
                if (stripos($content, '<html') !== false) {
                    $content = (new MinifyManager)->parse($content);

                    $response->setContent($content);
		        }

            }

        }

		return $response;
    }
}
