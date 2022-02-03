<?php

/**
 * @file
 * Handles counts of node views via AJAX with minimal bootstrap.
 */

use Drupal\Core\DrupalKernel;
use Symfony\Component\HttpFoundation\Request;

// Use SCRIPT_FILENAME rather than the current filename so that symlinks are not
// resolved.
$app_root = dirname($_SERVER['SCRIPT_FILENAME'], 4);
chdir($app_root);

$autoloader = require_once 'autoload.php';

$kernel = DrupalKernel::createFromRequest(Request::createFromGlobals(), $autoloader, 'prod');
$kernel->boot();
$container = $kernel->getContainer();

$views = $container
  ->get('config.factory')
  ->get('statistics.settings')
  ->get('count_content_views');

if ($views) {
  $nid = filter_input(INPUT_POST, 'nid', FILTER_VALIDATE_INT);
  if ($nid) {
    $container->get('request_stack')->push(Request::createFromGlobals());
    $container->get('statistics.storage.node')->recordView($nid);
  }
}
