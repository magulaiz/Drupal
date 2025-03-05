#!/usr/bin/env php
<?php

/**
 * @file
 * Updates CLDR codes in CountryManager.php to latest data.
 *
 * We rely on the CLDR data set, because it is easily accessible, scriptable,
 * and in the right human-readable format.
 */

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

// Load the autoloader.
$autoloader = require_once DRUPAL_ROOT . '/autoload.php';

// Read in existing codes.
// @todo Allow to remove previously existing country codes.
// @see https://www.drupal.org/node/1436754
require_once DRUPAL_ROOT . '/core/includes/bootstrap.inc';

$request = Request::createFromGlobals();
$kernel = DrupalKernel::createFromRequest($request, $autoloader, 'prod');
$kernel->boot();

// Determine source data file URI to process.
$uri = DRUPAL_ROOT . '/territories.json';

if (!file_exists($uri)) {
  $usage = <<< USAGE
- Download territories.json from:
  https://github.com/unicode-org/cldr-json/blob/main/cldr-json/cldr-localenames-full/main/en/territories.json
  and place it in the Drupal root directory.
- Run this script.
USAGE;
  exit('CLDR data file not found. (' . $uri . ")\n\n" . $usage . "\n");
}

// Ensure database connection is available.
try {
  $container = $kernel->getContainer();
  $database = \Drupal::database();
  if (!$database) {
    throw new \Exception("Database connection not available.");
  }

  $country_manager = $container->get('country_manager');
  $countries = $country_manager->getStandardList();

  // Parse the source data into an array.
  $data = json_decode(file_get_contents($uri));

  foreach ($data->main->en->localeDisplayNames->territories as $code => $name) {
    // Use any alternate codes the Drupal community wishes to.
    $alt_codes = [
    // 'CI-alt-variant', // Use CI-alt-variant instead of the CI entry.
   ];
    if (in_array($code, $alt_codes)) {
      // Just use the first 2 character part of the alt code.
      $code = strtok($code, '-');
    }

    // Skip any codes we wish to exclude from our country list.
		// The European Union, The Eurozone, The United Nations, "Pseudo-Accents", "Pseudo-Bidi" is not a country.
		// Don't allow "Unknown Region".
    $exclude_codes = ['EU', 'EZ', 'UN', 'XA', 'XB', 'ZZ'];
    if (in_array($code, $exclude_codes)) {
      continue;
    }

    // Ignore non-ISO 3166-1 alpha-2 codes (must be exactly 2 characters).
    if (strlen($code) !== 2) {
      continue;
    }

    $countries[(string) $code] = $name;
  }

  if (empty($countries)) {
    echo 'ERROR: Did not find expected country names.' . PHP_EOL;
    exit;
  }

  // Sort countries by code.
  ksort($countries);

  // Generate the PHP array code.
  $out = '';
  foreach ($countries as $code => $name) {
    $name = str_contains($name, "'") ? '"' . $name . '"' : "'" . $name . "'";
    $out .= '      ' . var_export($code, TRUE) . ' => t(' . $name . '),' . "\n";
  }

  // Replace the actual PHP code in standard.inc.
  $file = DRUPAL_ROOT . '/core/lib/Drupal/Core/Locale/CountryManager.php';
  $content = file_get_contents($file);
  $content = preg_replace('/(\$countries = \[\n)(.+?)(^\s+\];)/ms', '$1' . $out . '$3', $content, -1, $count);
  file_put_contents($file, $content);

  echo "Country list updated successfully in CountryManager.php.\n";

}
catch (\Exception $e) {
  echo "Error: " . $e->getMessage() . "\n";
  exit(1);
}
