<?php

namespace Drupal\cache_flush_uninstall\Hook;

use Drupal\Core\Hook\Attribute\Hook;

/**
 * Hook implementations for cache_flush_uninstall.
 */
class cacheFlushUninstallHooks {

  /**
   * Implements hook_cache_flush().
   */
  #[Hook('cache_flush')]
  public function cacheFlush() {
    // Set a global value we can check in test code.
    $GLOBALS['hook_cache_flush'] = 'hook_cache_flush';
  }
}
