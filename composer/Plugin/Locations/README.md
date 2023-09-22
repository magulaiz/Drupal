# Drupal Locations Composer Plugin

This is a Composer plugin which allows Drupal to determine its location within
the project at runtime.

This plugin is not necessary when Drupal is installed one of the following
configurations:
    - Installed with the drupal/recommended-project Composer template,
    - Installed with the drupal/legacy-project Composer template,
    - Installed directly from a Drupal core git clone.

In these configurations, Drupal is able to guess its location.

This plugin writes a PHP class file automatically, which holds the location of
the Drupal app root (that is, the location of Drupal's index.php file).

This file is written to the root of the Composer project, and must be specified
in the root composer.json:

```
    "autoload": {
        "psr-4": {
            "Drupal\\Locations\\": ""
        }
    },
```

Because it is an autoloadable file, any code that has included the Composer
autoloader may use the \Drupal\Locations\DrupalLocation::APP_ROOT to get the
filepath where Drupal core is installed.

## API

Code that is part of a project's own codebase, and therefore can guarantee the
presence of this plugin, can use the \Drupal\Locations\DrupalLocation::APP_ROOT
constant to get the filepath where Drupal core is installed. Because this is an
autoloadable file, any code that has included the Composer autoloader may use
this.

If your code is not part of a project, but is a package that may be included in
projects, it can either require this plugin, or use
\Drupal\Core\DrupalKernel::getApplicationRoot().

## Background

This plugin necessary because Drupal core is installed in a Composer project in
a custom location, unlike normal Composer packages. The location of the Drupal
core package is defined in the project's composer.json, and for example, is
'/web' when using the drupal/recommended-project Composer project template.

Without this plugin, Drupal core's custom location means that web requests to
Drupal core PHP files must guess how to reach the Composer autoloader in the
project root, and script files in normal Composer packages must guess how to
reach Drupal core. Using the `__DIR__` constant and other filepath manipulation
is not always reliable, for example, in a development project where Drupal core
is installed with a symlink from a local path repository.

## Development

Use the `composer drupal:locations` command to write the DrupalLocations file.
This saves having to force Composer to install or remove a package to trigger
the file write.
