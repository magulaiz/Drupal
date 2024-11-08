<?php

namespace Drupal\Core\Field\TypedData;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\OptionsProviderInterface;

/**
 * Interface for options providers requiring field definitions.
 */
interface FieldStorageDefinitionAwareOptionsProviderInterface extends OptionsProviderInterface {

  /**
   * Sets the field storage definition.
   *
   * @param \Drupal\Core\Field\FieldStorageDefinitionInterface $definition
   *   The field definition where the options provider is defined.
   *
   * @return $this
   */
  public function setFieldStorageDefinition(FieldStorageDefinitionInterface $definition);

}
