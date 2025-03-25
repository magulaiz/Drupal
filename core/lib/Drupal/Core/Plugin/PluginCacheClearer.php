<?php

namespace Drupal\Core\Plugin;

use Drupal\Core\Cache\CacheClearableInterface;

/**
 * A cache clearer for plugins.
 */
class PluginCacheClearer implements CacheClearableInterface {

  public function __construct() {}

  public function clearCache(): void {

  }

}
