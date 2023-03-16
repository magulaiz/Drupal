<?php

namespace Drupal\Core\Config;

use Drupal\Core\Cache\Cache;

/**
 * Defines a config object that will not use config schema during save.
 */
class NoSchemaConfig extends Config {

  /**
   * {@inheritdoc}
   */
  public function save($has_trusted_data = FALSE) {
    if (func_num_args() > 0) {
      throw new \BadMethodCallException('This implementation does not support the $has_trusted_data argument');
    }
    // Validate the configuration object name before saving.
    static::validateName($this->name);

    // Potentially configuration schema could have changed the underlying data's
    // types.
    $this->resetOverriddenData();

    $this->storage->write($this->name, $this->data);
    if (!$this->isNew) {
      Cache::invalidateTags($this->getCacheTags());
    }
    $this->isNew = FALSE;
    $this->eventDispatcher->dispatch(new ConfigCrudEvent($this), ConfigEvents::SAVE);
    $this->originalData = $this->data;
    return $this;
  }

}
