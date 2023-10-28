#!/usr/bin/env php
<?php

/**
 * @file
 * A script to generate proxy classes for lazy services.
 *
 * For help, type this command from the root directory of an installed Drupal
 * site: php core/scripts/generate-proxy-class.php -h generate-proxy-class
 *
 * @ingroup container
 *
 * @see lazy_services
 *
 * @deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. The native
 *   lazy services from Symfony are now being used. Therefore, there is no need
 *   for Drupal proxy classes anymore. There is no replacement.
 *
 * @see https://www.drupal.org/node/3397076
 */

use Drupal\Core\Command\GenerateProxyClassApplication;
use Drupal\Core\DrupalKernel;
use Drupal\Core\ProxyBuilder\ProxyBuilder;
use Drupal\Core\Site\Settings;
use Symfony\Component\HttpFoundation\Request;

if (PHP_SAPI !== 'cli') {
  return;
}

// Bootstrap.
$autoloader = require __DIR__ . '/../../autoload.php';
$request = Request::createFromGlobals();
Settings::initialize(dirname(__DIR__, 2), DrupalKernel::findSitePath($request), $autoloader);
DrupalKernel::createFromRequest($request, $autoloader, 'prod')->boot();

// Run the database dump command.
$application = new GenerateProxyClassApplication(new ProxyBuilder());
$application->run();
