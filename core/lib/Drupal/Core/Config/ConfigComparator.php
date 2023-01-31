<?php

namespace Drupal\Core\Config;

use Drupal\Component\Utility\Crypt;

/**
 * The ConfigComparator provides helper functions for the configuration system.
 */
class ConfigComparator implements ConfigComparatorInterface {

  /**
   * The active configuration storage.
   */
  protected StorageInterface $activeStorage;

  /**
   * Creates ConfigComparator objects.
   *
   * @param \Drupal\Core\Config\StorageInterface $active_storage
   *   The active configuration storage.
   */
  public function __construct(StorageInterface $active_storage) {
    $this->activeStorage = $active_storage;
  }

  /**
   * {@inheritdoc}
   */
  public function isModified($config_name) {
    $active = $this->activeStorage->read($config_name);

    if (!$active) {
      throw new ConfigNameException(
        sprintf('Configuration does not exist for "%s".', $config_name)
      );
    }

    // Get the hash created when the config was installed.
    $original_hash = $active['_core']['default_config_hash'];

    // Remove export keys not used to generate default config hash.
    unset($active['uuid']);
    unset($active['_core']);
    $active_hash = Crypt::hashBase64(serialize($active));

    return $original_hash !== $active_hash;
  }

}
