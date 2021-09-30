# Statamic Minify

Add on for Statamic v3 to combine and minify all CSS and JS files found in the page response.

## Features

* Conditionally minify CSS or JS
* Group together specific files using data-group on your link or script element
* No pre-processing or combining of files required

## Installation

Install by composer: `composer require thoughtco/statamic-minify`

A new config file will be published, and you can modify the minification settings in `config/thoughtco/minify.php`.