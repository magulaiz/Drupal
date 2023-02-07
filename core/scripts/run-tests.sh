<?php

/**
 * @file
 * This script runs Drupal tests from command line.
 *
 * @deprecated in drupal:10.1.0 and is removed from drupal 11.0.0. Use
 * run-tests.php instead.
 *
 * @see https://www.drupal.org/node/2948321
 */

@trigger_error(__FILE__ . ' is deprecated in drupal:10.1.x and is removed from drupal:11.0.0. Use run-tests.php instead. See https://www.drupal.org/node/2948321.', E_USER_DEPRECATED);
include __DIR__ . '/run-tests.php';
