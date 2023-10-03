<?php

namespace Drupal\Core\Plugin;

/**
 * Provides a trait for Drupal\Core\PreWarm\PreWarmableInterface.
 */
trait PreWarmablePluginManagerTrait {

  /**
   * Implements \Drupal\Core\PreWarm\PreWarmableInterface.
   */
  public function preWarm(): void {
    $this->getDefinitions();
  }

}
