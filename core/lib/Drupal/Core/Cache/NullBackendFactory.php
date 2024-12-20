<?php

declare(strict_types=1);

namespace Drupal\Core\Cache;

class NullBackendFactory implements CacheFactoryInterface {

  /**
   * {@inheritdoc}
   */
  public function get($bin) {
    return new NullBackend($bin);
  }

}
