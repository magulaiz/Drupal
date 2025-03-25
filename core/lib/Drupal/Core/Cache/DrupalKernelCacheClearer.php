<?php

namespace Drupal\Core\Cache;

/**
 * A cache clearer that rebuilds the Drupal Kernel.
 */
class DrupalKernelCacheClearer implements CacheClearableInterface {

  /**
   * {@inheritdoc}
   */
  public function clearCache(): void {
    $kernel = \Drupal::service('kernel');
    $kernel->invalidateContainer();
    $kernel->rebuildContainer();
  }

}
