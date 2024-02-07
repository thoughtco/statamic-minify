<?php

namespace Thoughtco\Minify\Replacers;

use Illuminate\Http\Response;
use Statamic\StaticCaching\Replacer;
use Thoughtco\Minify\Managers\MinifyManager;

class MinifyReplacer implements Replacer
{
    public function prepareResponseToCache(Response $response, Response $initial)
    {
        if (! $content = $response->getContent()) {
            return;
        }

        if (! stripos($content, '<html') !== false) {
            return;
        }

        $content = (new MinifyManager)->parse($content);

        $response->setContent($content);
    }

    public function replaceInCachedResponse(Response $response)
    {
        return;
    }
}
