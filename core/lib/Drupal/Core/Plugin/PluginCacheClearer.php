<?php

namespace Drupal\Core\Plugin;

use Drupal\Core\Cache\CacheClearerInterface;

/**
 * A cache clearer for plugins.
 */
class PluginCacheClearer implements CacheClearerInterface {

  public function __construct() {}

  public function clearCache(): void {

  }

}
