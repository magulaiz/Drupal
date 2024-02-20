<?php

// @codingStandardsIgnoreFile

/**
 * @file
 * Configuration file for reverse proxy support.
 *
 * This file is required for reverse proxy support. This is where you enable
 * the feature and set the reverse proxy addresses and headers. The settings
 * behave exactly as before for a better upgrade experience.
 *
 * To activate this feature, copy and rename it such that its path plus
 * filename is 'sites/reverse_proxy_settings.php'.
 *
 * @see \Drupal\Core\DrupalKernel::setTrustedProxies()
 */

// Enable the feature.
$settings['reverse_proxy'] = TRUE;
// Set the reverse proxy addresses and headers.
$settings['reverse_proxy_addresses'] = [$_SERVER['REMOTE_ADDR']];
$settings['reverse_proxy_header'] = 'X_FORWARDED_FOR';