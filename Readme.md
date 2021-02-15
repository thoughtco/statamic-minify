# Statamic Minify

Add on for Statamic v3 to minify all CSS and JS

To install:

```
composer require thoughtco/statamic-minify
```

Then run `composer update`

Modify your settings in `config/thoughtco/minify.php`

Create individual min groups by adding data-group="xxx" to the `<link>` or `<script>` tag.