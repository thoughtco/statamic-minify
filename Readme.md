# Minify

Add on for Statamic to minify all CSS and JS

Download to App/Addons/Minify

In your project root’s composer.json, add the package to the require and repositories sections, like so:

```
{
    ...

    "require": {
        ...,
        "thoughtco/minify": "*"
    },

    ...

    "repositories": [
        {
            "type": "path",
            "url": "app/Addons/Minify"
        }
    ]
```

Then run `composer update`

