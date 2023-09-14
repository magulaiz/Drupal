<?php

namespace Drupal\Core\Cache;

class PhpBackendFactory implements CacheFactoryInterface {

  /**
   * Constructs a PhpBackendFactory object.
   *
   * @param \Drupal\Core\Cache\CacheTagsChecksumInterface $checksumProvider
   *   The cache tags checksum provider.
   */
  public function __construct(protected CacheTagsChecksumInterface $checksumProvider)
  {
  }

  /**
   * Gets PhpBackend for the specified cache bin.
   *
   * @param $bin
   *   The cache bin for which the object is created.
   *
   * @return \Drupal\Core\Cache\PhpBackend
   *   The cache backend object for the specified cache bin.
   */
  public function get($bin) {
    return new PhpBackend($bin, $this->checksumProvider);
  }

}
