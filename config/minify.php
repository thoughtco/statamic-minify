<?php

return [
    'file_types' => ['css', 'js'],
    'minify_enabled' => function(){
        return isset($_GET['no_min']) == false;
    }
];
