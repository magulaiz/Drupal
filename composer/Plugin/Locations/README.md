The Drupal Locations Composer Plugin
====================================

This project provides a composer plugin which allows Drupal to determine its
location within the project at runtime.

This is necessary because Drupal core is installed in a Composer project in a
custom location, unlike normal Composer packages. The location of the Drupal
core package is defined in the project's composer.json, and is '/web' when using
the drupal/recommended-project.

This custom location means that web requests to Drupal core PHP files must guess
how to reach the Composer autoloader in the project root, and script files in
normal Composer packages must guess how to reach Drupal core.

Using the `__DIR__` constant and other filepath manipulation is not always
reliable, for example, in a development project where Drupal core is installed
with a symlink from a local path repository.

This Composer plugin writes a PHP class file automatically, which holds the
location of the Drupal app root (that is, the location of Drupal's index.php
file).

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

Development
-----------

Use the `composer drupal:locations` command to write the DrupalLocations file.
This saves having to force Composer to install or remove a package to trigger
the file write.
